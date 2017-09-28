const app = require('express')()
const server = require('http').Server(app)
const io = require('socket.io')(server)
const redis = require('redis')
const moment = require('moment-timezone')
require('dotenv').config({ path: '../.env' })

const createEvent = 'App\\Models\\Event'
const createCampaign = 'App\\Models\\Campaign'

server.listen(process.env.PORT_SERVER_NODE)
let userConnection = []
let userLogin = null
let listFriend = []

const redisClient = redis.createClient(process.env.REDIS_PORT, 'redis')
const client = redis.createClient(process.env.REDIS_PORT, 'redis')
const subClient = redis.createClient(process.env.REDIS_PORT, 'redis')
const notificationClient = redis.createClient(process.env.REDIS_PORT, 'redis')

subClient.subscribe('createCampaign', 'createEvent')
notificationClient.subscribe('inviteUser')
redisClient.subscribe('singleChat', 'groupChat', 'activies', 'noty')
redisClient.on('message', function (channel, data) {
    if (channel == 'activies') {
        data = JSON.parse(data)

        if (data.status && findIndexById(listFriend, data.userId) == -1) {
            userLogin = data.userId
            listFriend.push({ id: data.userId, listFriend: data.listFollow })
        }

        callOnline(io.sockets, listFriend, data.userId)
    } else {
        var message = JSON.parse(data)

        if (parseInt(message.to)) {
            io.sockets.in(message.to).in(message.from).emit(channel, data)
        } else {
            io.to(message.to).emit(channel, data)
        }
    }
})

notificationClient.on('message', function (channel, data) {
    let notification = JSON.parse(data)
    io.sockets.in(notification.data.to).emit('inviteUser', { data: notification })
})

redisClient.on('error', function (err) {
    client.lpush('err', 'Exception: ' + err)
});

client.on('error', function (err) {
    client.lpush('err', 'Exception: ' + err)
})

subClient.on('message', function (channel, data) {
    let activity = JSON.parse(data)

    if (activity.type == createEvent) {
        sendIfCreateEvent(activity)
    } else {
        sendIfCreateCampaign(activity)
    }
})

subClient.on('error', function (err) {
    client.lpush('err', 'Exception: ' + err)
})

io.on('connection', function (socket) {
    // set room for socket and add to userConnection
    socket.on('register', data => {
        var index = userConnection.findIndex(user => user.socketId === socket.id)

        // join socket to room if type is single chat else join it to group chat with name is hashtag campaign
        if (data.type) {
            socket.join(data.id)
        } else {
            socket.join('hashtag:' + data.id)
        }

        if (index == -1) {
            userConnection.push({ id: data.id, socketId: socket.id })
        }

        callOnline(socket, listFriend, userLogin)
    })

    socket.on('notification', data => {
        for (var index = 0; index < data.groups.length; index++) {
            socket.join('hashtag:' + data.groups[index].hashtag)
        }
    })

    socket.on('sendRequest', data => {
        io.sockets.in(data.userId).in(data.acceptId).emit('sendRequestSuccess', { data: data })
    })

    socket.on('acceptRequest', data => {
        let i = listFriend.findIndex(list => list.id == data.userId)

        if (i != -1) {
            listFriend[i].listFriend.push(data.acceptId)
        }

        let j = listFriend.findIndex(list => list.id == data.acceptId)

        if (j != -1) {
            listFriend[j].listFriend.push(data.userId)
        }

        callOnline(io.sockets, listFriend, userLogin)
        io.sockets.in(data.userId).in(data.acceptId).emit('acceptRequestSuccess', {
            status: true,
            data: data
        })
    })

    socket.on('rejectRequest', data => {
        let toReject = {
            userId: data.userId,
            rejectId: data.rejectId,
            index: -1
        }

        let toUser = {
            userId: data.userId,
            rejectId: data.rejectId,
            index: data.index
        }

        io.sockets.in(data.rejectId).emit('rejectRequestSuccess', { data: toReject })
        io.sockets.in(data.userId).emit('rejectRequestSuccess', { data: toUser })
    })

    socket.on('cancelRequest', data => {
        socket.emit('cancelRequestSuccess', { data: data })
        io.sockets.in(data.cancelId).emit('cancelRequestSuccess', { data: data })
        io.sockets.in(data.cancelId).in(data.userId).emit('rejectRequestSuccess', { data: data })
    })

    socket.on('unfriend', data => {
        io.sockets.in(data.userId).in(data.unfriendId).emit('unfriendSuccess', { data: data })
    })

    socket.on('markRead', data => {
        if (parseInt(data.receive)) {
            let key = (parseInt(data.receive) > parseInt(data.send)) ?
                parseInt(data.send) + '-' + parseInt(data.receive) :
                parseInt(data.receive) + '-' + parseInt(data.send)
            let now = moment().tz('Asia/Ho_Chi_Minh').format('MM-DD-YYYY HH:mm:ss')
            client.lrange(key, 0, 0, function (err, replies) {
                let idMessage = replies[0]
                let message = {
                    id: idMessage,
                    time: now
                }

                client.set('read' + key, JSON.stringify(message))
            })

            socket.to(data.receive).emit('read', { id: data.receive, status: true, time: now, readBy: data.send })
        }
    })

    socket.on('accept_donation', data => {
        io.sockets.in(data.room).emit('accept_donation', data.donate)
    })

    socket.on('disconnect', function () {
        var index = userConnection.findIndex(user => user.socketId === socket.id)

        if (index != -1) {
            var userId = userConnection[index].id
            userConnection.splice(index, 1)

            if (userConnection.findIndex(user => user.id === userId) == -1) {
                callOffline(socket, listFriend, userId)
            }
        }
    })

    socket.on('viewing_event', function (data) {
        socket.join(data)
    })

    socket.on('stop_view_even', function (data) {
        socket.leave(data)
    })

    socket.on('created_action', function (data) {
        io.sockets.in(data.room).emit('new_action_created', { action: data.newAction })
    })

    socket.on('update_action', function (data) {
        io.sockets.in(data.room).emit('update_data_action', { action: data.action })
    })

    socket.on('remove_action', function (data) {
        io.sockets.in(data.room).emit('delete_action', { actionId: data.actionId })
    })

    //like
    socket.on('likeActivity', function (data) {
        io.sockets.in(data.room).emit('newLike', data.newLike)
    })

    socket.on('viewing_like', function (data) {
        socket.join(data)
    })

    socket.on('stop_view_like', function (data) {
        socket.leave(data)
    })
})

function callOnline(socket, listFriend, userLogin) {
    let index = findIndexById(listFriend, userLogin)

    if (index != -1) {
        for (var i = 0; i < listFriend[index].listFriend.length; i++) {
            socket.in(listFriend[index].listFriend[i]).emit('online', { type: true, id: userLogin, status: true })
        }

        let listFriendOnline = []

        for (var i = 0; i < listFriend[index].listFriend.length; i++) {
            let indexUserOnline = findIndexById(userConnection, listFriend[index].listFriend[i])

            if (indexUserOnline != -1) {
                listFriendOnline.push({ id: userConnection[indexUserOnline].id, status: true })
            }
        }

        socket.emit('online', { type: false, listOnline: listFriendOnline })
    }
}

function callOffline(socket, listFriend, id) {
    let index = findIndexById(listFriend, id)

    if (index != -1) {
        for (var i = 0; i < listFriend[index].listFriend.length; i++) {
            socket.in(listFriend[index].listFriend[i]).emit('online', { type: true, id: listFriend[index].id, status: false })
        }

        listFriend.splice(index, 1)
    }

    return true
}

function findIndexById(array, id) {
    return array.findIndex(arr => arr.id == id)
}

function sendIfCreateEvent(data) {
    let room = 'hashtag:' + data.hashtag

    if (!Number(data.feature)) {
        io.sockets.in(room).emit('createEventSuccess', { data: data })
    } else {
        io.sockets.emit('createEventSuccess', { data: data })
    }
}

function sendIfCreateCampaign(data) {
    if (!Number(data.feature.value)) {
        return
    }

    io.sockets.emit('createCampaignSuccess', { data: data })
}
