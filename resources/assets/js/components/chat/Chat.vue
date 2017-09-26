<template>
    <div class="container">
        <div class="ui-block popup-chat col-md-4 col-md-offset-4"
            :style="'right:' + marginRight + 'px !important'">
            <div class="ui-block-title">

                <router-link v-if="type" :to="{ name: 'user.timeline', params: { slug: receiveSlug }}" class="title-name">
                    {{ receiveName }}
                </router-link>
                <router-link v-else :to="{ name: 'campaign.timeline', params: { slug: receiveSlug }}" class="title-name">
                    {{ receiveName }}
                </router-link>
                <div class="more">
                    <svg class="olymp-little-delete" @click="closeComponent">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-little-delete"></use>
                    </svg>
                </div>
            </div>
            <div class="mCustomScrollbar ps ps--theme_default ps--active-y"
                data-mcs-theme="dark"
                data-ps-id="08dcf30a-ed2f-f4fc-dd34-a543d06407f1"
                :id="replaceSpace(receiveUser)">
                <ul class="notification-list chat-message chat-message-field">
                    <div v-show="topLoadMore" class="top-load-more-chat">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <li v-for="(subMessage, index) in organizedMessage"
                        :class="subMessage[0].userId == user.id ? 'popup-chat-myself' : 'popup-chat-friend'">
                        <div class="author-thumb">
                            <img :src="subMessage[0].avatar" alt="author">
                        </div>
                        <div class="box-notification-event">
                            <div class="notification-event">
                                <span class="chat-message-item" v-for="mess in subMessage" v-html="mess.message"></span>
                                <span class="notification-date">
                                    <time class="entry-date updated"
                                        datetime="2004-07-24T18:18"
                                        v-if="index == (organizedMessage.length - 1)
                                            && read
                                            && messages[messages.length - 1].userId == user.id
                                            && readBy == receiveUser">
                                        {{ $t('messages.read_at') + calendarTime(readAt) }}
                                    </time>
                                    <time class="entry-date updated" datetime="2004-07-24T18:18"v-else>
                                        {{ calendarTime(organizedMessage[index][subMessage.length - 1].time) }}
                                    </time>
                                </span>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="ps__scrollbar-x-rail">
                    <div class="ps__scrollbar-x" tabindex="0"></div>
                </div>
                <div class="ps__scrollbar-y-rail">
                    <div class="ps__scrollbar-y" tabindex="0"></div>
                </div>
            </div>
            <form-chat
                v-model="content"
                @mark-read="markRead"
                @text-change="handleChat">
            </form-chat>
        </div>
    </div>
</template>

<script>
import { post, get } from '../../helpers/api'
import { sendMessage, sendMessageToGroup, showMessage } from '../../router/router'
import { mapState } from 'vuex'
import FormChat from '../libs/FormChat.vue'
import noty from '../../helpers/noty'
import { EventBus } from '../../EventBus.js'

export default {
    props: {
        receive: null,
        name: null,
        list: null,
        marginRight: 0,
        type: Boolean,
        index: {
            type: Number
        },
        slug: null
    },
    data: function () {
        return {
            messages: [],
            content: ``,
            receiveUser: this.receive,
            receiveName: this.name,
            receiveSlug: this.slug,
            groupChat: '',
            listChat: this.list,
            right: this.marginRight,
            paginate: 0,
            isLoad: false,
            continue: true,
            read: false,
            readAt: '',
            readBy: this.receive,
            topLoadMore: false
        }
    },
    watch: {
        listChat() {
            this.$emit('update:list', this.listChat)
        }
    },
    created () {
        if (this.receiveUser != '') {
            if (this.type) {
                if (this.user.id > this.receiveUser) {
                    this.groupChat = this.receiveUser + '-' + this.user.id
                } else {
                    this.groupChat = this.user.id + '-' + this.receiveUser
                }
            } else {
                this.groupChat = this.receiveUser
                this.$socket.emit('register', { id: this.groupChat, type: false })
            }

            this.getMessage()
        } else {
            const message = this.$i18n.t('chat.get_message_error')
            noty({ text: message, container: false, force: true})
        }
    },
    updated() {
        if (!this.isLoad) {
            $('#' + this.replaceSpace(this.receiveUser))
                .scrollTop($('#' + this.replaceSpace(this.receiveUser))[0].scrollHeight)
            this.isLoad = true
        }
    },
    methods: {
        handleChat() {
            if (this.content.trim() == '') {
                this.content = this.content.trim()

                return
            }

            let channel = null
            let link = null

            if (this.type) {
                channel = 'singleChat'
                link = sendMessage
            } else {
                channel = 'groupChat'
                link = sendMessageToGroup
            }

            post(link, {
                message: this.content.trim(),
                receiveUser: this.receiveUser,
                channel: channel,
                groupKey: this.groupChat
            }).then(res => {
                    if (res.data.status == 200) {
                        this.content = ''
                    } else {
                        this.messages.push(this.$i18n.t('chat.send_message_error'))
                    }
                })
                .catch(err => {
                    this.messages.push(this.$i18n.t('chat.send_message_error'))
                })
        },
        getMessage() {
            if (this.continue) {
                this.topLoadMore = true
                get(`${showMessage}/${this.receiveUser}?type=${this.type}&paginate=${this.paginate}`)
                    .then(res => {
                        if (res.data.status == 200) {
                            this.read = res.data.read
                            this.readAt = res.data.time
                            const heightBoxChatBefore = this.getScrollHeight()
                            for (var index = 0; index < res.data.messages.length; index++ ) {
                                let data = res.data.messages[index]

                                if (data) {
                                    let mess = {
                                        userId: data.userId,
                                        message: data.message,
                                        avatar: data.avatar,
                                        time: data.time
                                    }

                                    this.messages.unshift(mess)
                                }
                            }

                            this.$nextTick(function () {
                                if (this.paginate) {
                                    setTimeout(() => {
                                        const heightBoxChatAfter = this.getScrollHeight()
                                        this.scrollTopBoxChat(heightBoxChatAfter - heightBoxChatBefore)
                                    }, 0)
                                } else {
                                    this.scrollTopBoxChat()
                                }

                                this.paginate = res.data.paginate
                            })
                        }

                        this.continue = res.data.continue
                        setTimeout(() => {
                            this.topLoadMore = false
                        }, 0)
                    })
                    .catch(err => {
                        this.messages.push(this.$i18n.t('chat.get_message_error'))
                    })
            }
        },
        closeComponent() {
            this.$emit('deleteChatIndex', this.index)
        },
        replaceSpace(str) {
            let replaceStr = String(str)

            if (replaceStr.indexOf(' ')) {
                replaceStr.replace(' ', '')
            }

            return replaceStr
        },
        receiveMessage(data) {
            var socketData = JSON.parse(data)

            if (socketData.success && socketData.groupChat == this.groupChat) {
                var message = JSON.parse(socketData.message)
                let mess = {
                    userId: socketData.from,
                    message: message.message,
                    avatar: message.avatar,
                    time: message.time
                }

                this.messages.push(mess)
                this.paginate++
                this.read = false
                this.scrollTopBoxChat()
            }
        },
        calendarTime(time) {
            return moment(time, this.$i18n.t('chat.moment_format')).calendar()
        },
        addIcon(number) {
            this.content += "<img src='/images/icon-chat" + number + ".png' alt='icon'>"
        },
        markRead() {
            if (this.messages.length && this.messages[this.messages.length - 1].userId != this.user.id) {
                this.$socket.emit('markRead', {
                    receive: this.receiveUser,
                    send: this.user.id
                })

                EventBus.$emit('markRead', { receiveId: this.user.id, senderId: this.receiveUser })
            }
        },
        scrollTopBoxChat(scrollHeight) {
            // Stack service API
            setTimeout(() => {
                const elBox = $('#' + this.replaceSpace(this.receiveUser))
                const scroll = scrollHeight || elBox.prop('scrollHeight')
                elBox.scrollTop(scroll)
            }, 0)
        },
        getScrollHeight() {
            return $('#' + this.replaceSpace(this.receiveUser) + ' .chat-message-field').height()
        }
    },
    mounted() {
        $('#' + this.replaceSpace(this.receiveUser)).scroll(() => {
            if ($('#' + this.replaceSpace(this.receiveUser)).scrollTop() == 0) {
                this.getMessage()
            }
        })
    },
    computed: {
        ...mapState('auth', {
            user: state => state.user
        }),
        organizedMessage() {
            let messages = []
            let childMessage = []

            for (let i = 0; i < this.messages.length; i++) {
                if (this.messages.length != i + 1 && this.messages[i].userId != this.messages[i + 1].userId) {
                    childMessage.push(this.messages[i])
                    messages.push(childMessage)
                    childMessage = []
                } else {
                    childMessage.push(this.messages[i])

                    if (this.messages.length == i + 1) {
                        messages.push(childMessage)
                    }
                }
            }

            return messages
        }
    },
    sockets: {
        singleChat: function (data) {
            this.receiveMessage(data)
        },
        groupChat: function (data) {
            this.receiveMessage(data)
        },
        read: function (data) {
            this.read = data.status
            this.readAt = data.time
            this.readBy = data.readBy
        }
    },
    components: {
        FormChat
    }
}
</script>
<style src="./chat.scss" lang="scss" scoped></style>
