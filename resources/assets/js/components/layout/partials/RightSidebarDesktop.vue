<template lang="html">
    <div class="fixed-sidebar right" :class="{ open: isOpen }">
        <div class="fixed-sidebar-right sidebar--small" id="sidebar-right">
            <div class="mCustomScrollbar" data-mcs-theme="dark" id="listClose">
                <ul class="chat-users" v-for="friend in listUsers">
                    <li class="inline-items" @click="addChatComponent(friend.id, friend.name, true, friend.slug)">
                        <div class="author-thumb">
                            <img alt="author" :src="friend.image_thumbnail" class="avatar">
                            <span class="icon-status online" v-if="friend.online"></span>
                            <span class="icon-status disconected" v-else></span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="search-friend inline-items">
                <a href="javascript:void(0)" class="js-sidebar-open" @click="isOpen = true">
                    <svg class="olymp-menu-icon">
                        <use xlink:href='/frontend/icons/icons.svg#olymp-menu-icon'></use>
                    </svg>
                </a>
            </div>
            <a href="javascript:void(0)" class="olympus-chat inline-items">
                <svg class="olymp-chat---messages-icon">
                    <use xlink:href='/frontend/icons/icons.svg#olymp-chat---messages-icon'></use>
                </svg>
            </a>
        </div>
        <div class="fixed-sidebar-right sidebar--large" id="sidebar-right-1">
            <div class="mCustomScrollbar" data-mcs-theme="dark" id="listOpen">
                <div class="ui-block-title ui-block-title-small">
                    <a href="javascript:void(0)" class="title">{{ $t('chat.close_friends') }}s</a>
                    <a href="javascript:void(0)">{{ $t('chat.settings') }}</a>
                </div>
                <ul class="chat-users">
                    <li class="inline-items" v-for="friend in users">
                        <div class="author-thumb">
                            <img alt="author" :src="friend.image_thumbnail"
                                class="avatar"
                                @click="addChatComponent(friend.id, friend.name, true, friend.slug)">
                            <span class="icon-status online" v-if="friend.online"></span>
                            <span class="icon-status disconected" v-else></span>
                        </div>
                        <div class="author-status">
                            <a href="javascript:void(0)"
                                class="h6 author-name"
                                @click="addChatComponent(friend.id, friend.name, true, friend.slug)">
                                {{ friend.name }}
                            </a>
                            <span class="status">
                                {{ friend.online ? $t('user.connect.online') : $t('user.connect.offline') }}
                            </span>
                        </div>
                        <div class="more">
                            <svg class="olymp-three-dots-icon">
                                <use xlink:href='/frontend/icons/icons.svg#olymp-three-dots-icon'></use>
                            </svg>
                            <ul class="more-icons">
                                <li>
                                    <svg data-toggle="tooltip"
                                        data-placement="top"
                                        data-original-title="START CONVERSATION"
                                        class="olymp-comments-post-icon"
                                        @click="addChatComponent(friend.id, friend.name, true)">
                                        <use xlink:href='/frontend/icons/icons.svg#olymp-comments-post-icon'></use>
                                    </svg>
                                </li>
                                <li>
                                    <svg data-toggle="tooltip" data-placement="top" data-original-title="ADD TO CONVERSATION" class="olymp-add-to-conversation-icon">
                                        <use xlink:href='/frontend/icons/icons.svg#olymp-add-to-conversation-icon'></use>
                                    </svg>
                                </li>
                                <li>
                                    <svg data-toggle="tooltip" data-placement="top" data-original-title="BLOCK FROM CHAT" class="olymp-block-from-chat-icon">
                                        <use xlink:href='/frontend/icons/icons.svg#olymp-block-from-chat-icon'></use>
                                    </svg>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
                <div class="ui-block-title ui-block-title-small">
                    <a href="javascript:void(0)" class="title">{{ $t('chat.groups') }}</a>
                    <a href="javascript:void(0)">{{ $t('chat.settings') }}</a>
                </div>
                <ul class="chat-users">
                    <li class="inline-items" v-for="group in groupsCampaign">
                        <div class="author-thumb">
                            <img alt="author"
                                :src="group.media[0].image_thumbnail"
                                class="avatar"
                                @click="addChatComponent(group.hashtag, group.hashtag, false, group.slug)">
                            <span class="icon-status online"></span>
                        </div>
                        <div class="author-status">
                            <a href="javascript:void(0)"
                                class="h6 author-name"
                                @click="addChatComponent(group.hashtag, group.hashtag, false, group.slug)">
                                {{ group.hashtag }}
                            </a>
                            <span class="status"></span>
                        </div>
                        <div class="more">
                            <svg class="olymp-three-dots-icon">
                                <use xlink:href='/frontend/icons/icons.svg#olymp-three-dots-icon'></use>
                            </svg>
                            <ul class="more-icons">
                                <li>
                                    <svg data-toggle="tooltip"
                                        data-placement="top"
                                        data-original-title="START CONVERSATION"
                                        class="olymp-comments-post-icon"
                                        @click="addChatComponent(group.hashtag, group.hashtag, false, group.slug)">
                                        <use xlink:href='/frontend/icons/icons.svg#olymp-comments-post-icon'></use>
                                    </svg>
                                </li>
                                <li>
                                    <svg data-toggle="tooltip" data-placement="top" data-original-title="ADD TO CONVERSATION" class="olymp-add-to-conversation-icon">
                                        <use xlink:href='/frontend/icons/icons.svg#olymp-add-to-conversation-icon'></use>
                                    </svg>
                                </li>
                                <li>
                                    <svg data-toggle="tooltip" data-placement="top" data-original-title="BLOCK FROM CHAT" class="olymp-block-from-chat-icon">
                                        <use xlink:href='/frontend/icons/icons.svg#olymp-block-from-chat-icon'></use>
                                    </svg>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="search-friend inline-items">
                <div class="form-group">
                    <input class="form-control"
                        :placeholder="$t('chat.search_friends')"
                        type="text"
                        v-model="searchText">
                </div>
                <a href="javascript:void(0)" class="settings">
                    <svg class="olymp-settings-icon">
                        <use xlink:href='/frontend/icons/icons.svg#olymp-settings-icon'></use>
                    </svg>
                </a>
                <a href="javascript:void(0)" class="js-sidebar-open" @click="isOpen = false">
                    <svg class="olymp-close-icon">
                        <use xlink:href='/frontend/icons/icons.svg#olymp-close-icon'></use>
                    </svg>
                </a>
            </div>
            <a href="javascript:void(0)" class="olympus-chat inline-items">
                <h6 class="olympus-chat-title">{{ $t('chat.campaign_chat') }}</h6>
                <svg class="olymp-chat---messages-icon">
                    <use xlink:href='/frontend/icons/icons.svg#olymp-chat---messages-icon'></use>
                </svg>
            </a>
        </div>

        <div>
            <chat v-for="(chat, index) in chats"
                :receive="chat.id"
                :key="chat.id"
                :slug="chat.slug"
                :name="chat.name"
                :type="chat.type"
                :list.sync="chats"
                :index="index"
                :marginRight="chat.marginRight"
                @deleteChatIndex="handleDeleteChatIndex">
            </chat>
        </div>
    </div>

</template>

<script>
import { get } from '../../../helpers/api'
import { follow } from '../../../router/router'
import { mapState, mapActions } from 'vuex'
import Chat from '../../../components/chat/Chat.vue'
import noty from '../../../helpers/noty'
import { EventBus } from '../../../EventBus.js'

export default {
    data: () => ({
        isOpen: false,
        chats: [],
        marginRight: 0,
        widthDefault: 320,
        marginDefault: 70,
        marginWhenOpen: 280,
        maxList: 3,
        listOnline: [],
        searchText: ''
    }),
    computed: {
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user,
            friends: state => state.listContact,
            groups: state => state.groups
        }),
        listUsers() {
            let listUsers = []

            for (var index = 0; index < this.friends.length; index++) {
                let check = this.listOnline.findIndex(user => user.id == this.friends[index].id)
                listUsers.push({
                    id: this.friends[index].id,
                    name: this.friends[index].name,
                    email: this.friends[index].email,
                    online: (check != -1) ? this.listOnline[check].status : false,
                    image_thumbnail: this.friends[index].image_thumbnail,
                    slug: this.friends[index].slug
                })
            }

            return listUsers
        },
        users() {
            return this.listUsers.filter(user => {
                return user.name.toLowerCase().includes(this.searchText.toLowerCase())
                    || user.email.toLowerCase().includes(this.searchText.toLowerCase())
            })
        },
        groupsCampaign() {
            return this.groups.filter(group => {
                return group.title.toLowerCase().includes(this.searchText.toLowerCase())
                    || group.hashtag.toLowerCase().includes(this.searchText.toLowerCase())
            })
        }
    },
    watch: {
        isOpen() {
            //set margin when open or close list choose friend
            if (!this.isOpen && this.chats.length) {
                this.chats[0].marginRight = this.marginDefault

                for (var index = 1; index < this.chats.length; index++) {
                    this.chats[index].marginRight = this.chats[index - 1].marginRight + this.widthDefault
                }
            }

            if (this.isOpen && this.chats.length) {
                this.chats[0].marginRight = this.marginWhenOpen

                for (var index = 1; index < this.chats.length; index++) {
                    this.chats[index].marginRight = this.chats[index - 1].marginRight + this.widthDefault
                }
            }
        }
    },
    created () {
        if (this.authenticated) {
            this.getListFollow()
                .then(res => {
                    if (res) {
                        this.$socket.emit('register', { id: this.user.id, type: true })
                        this.emitListCampaign()
                        EventBus.$emit('getListFriends')
                    }
                })
                .catch(err => {
                    const message = this.$i18n.t('messages.connection_error')
                    noty({ text: message, container: false, force: true })
                })

            EventBus.$on('addChatComponent', (data) => {
                this.addChatComponent(data.id, data.name, data.singleChat, data.slug)
            })
        }
    },
    methods: {
        ...mapActions('auth', [
            'getListFollow'
        ]),
        addChatComponent(id, name, singleChat, slug) {
            var flag = true

            //check if conponent chat is exists in array chats
            for (var index = 0; index < this.chats.length; index++) {
                if (this.chats[index].id == id) {
                    flag = false
                }
            }

            if (flag) {
                if (this.chats.length) {
                    this.marginRight = this.chats[this.chats.length - 1].marginRight + this.widthDefault
                } else {
                    if (this.isOpen) {
                        this.marginRight = this.marginWhenOpen
                    } else {
                        this.marginRight = this.marginDefault
                    }
                }

                let chat = {
                    id: id,
                    name: name,
                    marginRight: this.marginRight,
                    type: singleChat,
                    slug: slug
                }

                this.chats.push(chat)

                if (this.chats.length > this.maxList) {
                    this.handleDeleteChatIndex(0)
                }
            }
        },
        handleDeleteChatIndex(index) {
            for (var i = index + 1; i < this.chats.length; i++) {
                this.chats[i].marginRight -= this.widthDefault
            }

            this.marginRight -= this.widthDefault
            this.chats.splice(index, 1)
        },
        emitListCampaign() {
            const listGroups = []

            for (var index = 0; index < this.groups.length; index++) {
                let group = {
                    campaignId: this.groups[index].id,
                    hashtag: this.groups[index].hashtag
                }

                listGroups.push(group)
            }

            this.$socket.emit('notification', { userId: this.user.id, groups: listGroups })
        }
    },
    components: {
        Chat
    },
    sockets: {
        singleChat: function (data) {
            var socketData = JSON.parse(data)

            if (socketData.success && socketData.to == this.user.id) {
                this.addChatComponent(socketData.from, socketData.name, true)
            }

            if (socketData.success && socketData.from == this.user.id) {
                for (var index = 0; index < this.friends.length; index++) {
                    if (this.friends[index].id == socketData.to) {
                        this.addChatComponent(socketData.to, this.friends[index].name, true)
                        break
                    }
                }
            }
        },
        online: function (data) {
            if (data.type) {
                let index = this.listOnline.findIndex(user => user.id == data.id)

                if (index == -1) {
                    this.listOnline.push({ id: data.id, status: data.status })
                } else {
                    this.listOnline[index].status = data.status
                }
            } else {
                for (var index = 0; index < data.listOnline.length; index++) {
                    this.listOnline.push({
                        id: data.listOnline[index].id,
                        status: data.listOnline[index].status
                    })
                }
            }
        }
    }
}
</script>

<style lang="scss">
#listOpen, #listClose {
    overflow-y: scroll;
}

#listClose::-webkit-scrollbar, #listOpen::-webkit-scrollbar
{
	width: 0px;
	background-color: #F5F5F5;
}

.fixed-sidebar-right.sidebar--large {
    width: 280px;
}
</style>
