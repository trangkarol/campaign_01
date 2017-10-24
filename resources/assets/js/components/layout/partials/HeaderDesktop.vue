<template lang="html">
    <header :class="{
        header: true,
        'model-action': showAction
        }" id="site-header">
        <div class="page-title">
            <h6>
                <router-link :to="{ name: 'homepage' }">
                    {{ $t('homepage.home') }}
                </router-link>
            </h6>
        </div>
        <div class="header-content-wrapper">
            <div class="search-bar w-search notification-list friend-requests" v-if="user">
                <div class="form-group with-button">
                    <input class="form-control js-user-search"
                        v-model="keyword"
                        @keyup.enter="searchRedirect"
                        @input="search"
                        :placeholder="$t('user.header.search')"
                        type="text">
                    <button>
                        <svg class="olymp-magnifying-glass-icon">
                            <use xlink:href="/frontend/icons/icons.svg#olymp-magnifying-glass-icon"></use>
                        </svg>
                    </button>
                    <div class="selectize-dropdown multi form-control js-user-search">
                        <div class="selectize-dropdown-content">
                            <div class="inline-items" v-for="(result, index) in usersFinded" v-if="index < 3">
                                <div class="author-thumb">
                                    <img class="avatar" :src="result.image_small" alt="avatar">
                                </div>
                                <div class="notification-event">
                                    <router-link class="h6 notification-friend" :to="{ name: 'user.timeline', params: { slug: result.slug }}">
                                        {{ result.name }}
                                    </router-link>
                                    <span class="chat-message-item">
                                        {{ result.email }}
                                    </span>
                                </div>
                                <span class="notification-icon">
                                    <svg class="olymp-happy-face-icon">
                                        <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                                    </svg>
                                </span>
                            </div>
                            <div class="result-campaign inline-items" v-for="(result, index) in campaignsFinded" v-if="index < 3">
                                <div class="author-thumb">
                                    <img class="img-campaign" v-if="result.media.length" :src="result.media[0].image_small" alt="avatar">
                                </div>
                                <div class="notification-event">
                                    <router-link class="h6 notification-friend" :to="{ name: 'campaign.timeline', params: { slug: result.slug }}">
                                        <span v-if="result.title.length < 45">{{ result.title }}</span>
                                        <span v-else>{{ result.title.substr(0, 45) }} ...</span>
                                    </router-link>
                                    <p class="hashtag">@{{ result.hashtag }}</p>
                                    {{ $t('user.header.tag') }}:
                                    <span class="chat-message-item">
                                        <span v-for="tag in result.tags">
                                            <span class="tag-info">{{ tag.name }}</span>
                                        </span>
                                    </span>
                                </div>
                                <span class="notification-icon">
                                    <svg class="olymp-star-icon">
                                        <use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-block" v-if="authenticated">
                <!-- Component friends -->
                <div class="control-icon more div-friend has-items">
                    <svg class="olymp-happy-face-icon" @click.stop="markRead(0)">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                    </svg>
                    <div class="label-avatar bg-blue" v-show="count">{{ count }}</div>
                    <div id="friend-suggest" class="more-dropdown more-with-triangle triangle-top-center" v-show="showFriend">
                        <div class="ui-block-title ui-block-title-small">
                            <h6 class="title">{{ $t('messages.friend_popup') }}</h6>
                            <a href="javascript:void(0)" style="display: none;">{{ $t('messages.find_friend') }}</a>
                            <a href="javascript:void(0)" style="display: none;">{{ $t('messages.settings') }}</a>
                        </div>
                        <div class="mCustomScrollbar" id="notification_list_request" data-mcs-theme="dark">
                            <ul class="notification-list friend-requests">
                                <li v-for="request in listRequest" :class="request.accept ? 'accepted' : ''">
                                    <div class="author-thumb">
                                        <img :src="request.avatar" alt="author" id="img-author-showAvatar">
                                    </div>
                                    <div class="notification-event" v-if="!request.accept">
                                        <router-link :to="{ name: 'user.timeline', params: { slug: request.userId } }"
                                            class="h6 notification-friend">
                                                {{ request.userName }}
                                            <span class="author-subtitle">{{ request.email }}</span>
                                        </router-link>
                                    </div>
                                    <div class="notification-event" v-else>
                                        {{ $t('messages.you_and') }}
                                        <router-link :to="{ name: 'user.timeline', params: { slug: request.userId } }"
                                            class="h6 notification-friend">
                                                {{ request.userName }}
                                        </router-link>
                                        {{ $t('messages.became_friend') }}.
                                    </div>
                                    <span class="notification-icon" v-if="!request.accept">
                                        <a href="javascript:void(0)"
                                            @click="acceptRequest(request.id, request.userId)"
                                            class="accept-request">
                                            <span class="icon-add without-text">
                                                <svg class="olymp-happy-face-icon">
                                                    <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                                                </svg>
                                            </span>
                                        </a>
                                        <a href="javascript:void(0)"
                                            @click="rejectRequest(request.id, request.userId)"
                                            class="accept-request request-del">
                                            <span class="icon-minus">
                                                <svg class="olymp-happy-face-icon">
                                                    <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                                                </svg>
                                            </span>
                                        </a>
                                    </span>
                                    <div class="more">
                                        <svg class="olymp-three-dots-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                        </svg>
                                    </div>
                                </li>
                                <li class="notification-empty" v-if="!listRequest.length">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                    {{ $t('homepage.header.invite_empty') }}
                                </li>
                            </ul>
                        </div>
                        <a href="javascript:void(0)" @click="getListRequest()" class="view-all bg-blue" v-if="count">
                            {{ $t('messages.show_more') }}
                        </a>
                         <a href="javascript:void(0)" class="view-all bg-blue" v-else></a>
                    </div>
                </div>

                <!-- Component chat -->
                <div class="control-icon div-messages more has-items">
                    <svg class="olymp-chat---messages-icon" @click.stop="showChatMessages">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-chat---messages-icon"></use>
                    </svg>
                    <div class="label-avatar bg-purple" v-show="countReadMessage">{{ countReadMessage }}</div>
                    <div id="show-messages" class="more-dropdown more-with-triangle triangle-top-center" v-show="showMessage">
                        <div class="ui-block-title ui-block-title-small">
                            <h6 class="title">{{ $t('homepage.header.chat_message') }}</h6>
                            <a href="#" style="display:none;">Mark all as read</a>
                            <a href="#" style="display:none;">Settings</a>
                        </div>
                        <div class="mCustomScrollbar" data-mcs-theme="dark" id="notification_messages">
                            <ul class="notification-list chat-message">
                                <li v-for="mess in messages" :class="mess.class" @click="addChatComponent(mess)">
                                    <div class="author-thumb">
                                        <img :src="mess.showAvatar" alt="author" id="img-author-showAvatar">
                                    </div>
                                    <div class="notification-event">
                                        <a href="#" class="h6 notification-friend">{{ mess.showName }}</a>
                                        <span id="chat-notification" class="chat-message-item"
                                            v-html="mess.sendName + mess.message">
                                        </span>
                                        <span class="notification-date">
                                            <time class="entry-date updated" v-if="mess.read">
                                                {{ $t('homepage.header.readed_at') + ' ' + calendarTime(mess.time) }}
                                            </time>
                                            <time class="entry-date updated" v-else>
                                                {{ calendarTime(mess.time) }}
                                            </time>
                                        </span>
                                    </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-chat---messages-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-chat---messages-icon"></use>
                                        </svg>
                                    </span>
                                    <div class="more">
                                        <svg class="olymp-three-dots-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                        </svg>
                                    </div>
                                </li>
                                <li class="notification-empty" v-if="!messages.length">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                    {{ $t('homepage.header.message_empty') }}
                                </li>
                            </ul>
                        </div>
                        <a href="javascript:void(0)" class="view-all bg-purple"></a>
                    </div>
                </div>

                <!-- Component notification -->
                <div class="control-icon more div-notification has-items">
                    <svg class="olymp-thunder-icon" @click.stop="getListNotification">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-thunder-icon"></use>
                    </svg>
                    <div class="label-avatar bg-primary" v-if="totalUnreadNotifications">
                        {{ totalUnreadNotifications }}
                    </div>
                    <div id="detail-notification" class="more-dropdown more-with-triangle triangle-top-center" v-show="show">
                        <div class="ui-block-title ui-block-title-small">
                            <h6 class="title">{{ $t('homepage.header.notifications') }}</h6>
                            <a href="#" style="display: none;">Mark all as read</a>
                            <a href="#" style="display: none;">Settings</a>
                        </div>
                        <div class="mCustomScrollbar" data-mcs-theme="dark">
                            <ul class="ul-notification notification-list">
                                <notification v-for="(notification, index) in listNotification"
                                    :notification="notification"
                                    :key="index"
                                    :totalUnreadNotifications.sync="totalUnreadNotifications"
                                    :show.sync="show"
                                    :showAction.sync="showAction"
                                    :dataAction.sync="dataAction.list_action"
                                    :checkLikeActions.sync="dataAction.checkLikeAction"
                                    :checkPermission.sync="checkPermission">
                                    >
                                </notification>
                                <li class="li-loading" v-show="loading">
                                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                                    <span>{{ $t('homepage.header.loading') }}</span>
                                </li>
                                <li class="notification-empty" v-if="!listNotification.length && !loading">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                    {{ $t('homepage.header.notification_empty') }}
                                </li>
                            </ul>
                        </div>
                        <a href="javascript:void(0)" class="view-all bg-primary"></a>
                    </div>
                </div>

                <!-- Component profile -->
                <div class="author-page author vcard inline-items more">
                    <div class="author-thumb">
                        <img alt="author" :src="user.image_thumbnail" class="avatar">
                        <span class="icon-status online"></span>
                        <div class="more-dropdown more-with-triangle">
                            <div class="ui-block-title ui-block-title-small">
                                <h6 class="title">{{ $t('homepage.header.your_account') }}</h6>
                            </div>
                            <ul class="account-settings">
                                <li>
                                    <router-link :to="{ name: 'setting.profile' }">
                                        <svg class="olymp-menu-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-menu-icon"></use>
                                        </svg>
                                        <span>{{ $t('homepage.header.profile_settings') }}</span>
                                    </router-link>
                                </li>
                                <li>
                                    <router-link :to="{ name: 'user.timeline', params: { slug: user.slug }}">
                                        <svg class="olymp-star-icon left-menu-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use>
                                        </svg>
                                        <span>{{ $t('homepage.header.your_timeline') }}</span>
                                    </router-link>
                                </li>
                                <li>
                                    <a @click="handleLogout">
                                        <svg class="olymp-logout-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-logout-icon"></use>
                                        </svg>
                                        <span>{{ $t('homepage.header.logout') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <router-link :to="{ name: 'user.timeline', params: { slug: user.slug } }" class="author-name fn">
                        <div class="author-title">
                            {{ user.name }}
                            <svg class="olymp-dropdown-arrow-icon">
                                <use xlink:href="/frontend/icons/icons.svg#olymp-dropdown-arrow-icon"></use>
                            </svg>
                        </div>
                        <span class="author-subtitle">{{ user.email }}</span>
                    </router-link>
                </div>

                <!-- Component languages -->
                <div class="select-lang control-icon more has-items">
                    <img src="/images/vn.png" v-show="lang == 'vi'" alt="author">
                    <img src="/images/jp5.png" v-show="lang == 'ja'" alt="author">
                    <img src="/images/en.png" v-show="lang == 'en'" alt="author">
                    {{ $t('homepage.header.language') }}
                    <div class="more-lang more-dropdown more-with-triangle triangle-top-center">
                        <div class="ui-block-title ui-block-title-small">
                            <h6 class="title">{{ $t('homepage.header.choose_language') }}</h6>
                        </div>
                        <div class="mCustomScrollbar ps ps--theme_default ps--active-y" data-mcs-theme="dark">
                            <ul class="notification-list friend-requests">
                                <li @click.prevent="changeLanguage('vi')">
                                    <div class="author-thumb">
                                        <img src="/images/vn.png" alt="author">
                                    </div>
                                    <div class="notification-event">
                                        <a href="javascript:void(0)" class="h6 notification-friend">
                                            {{ $t('homepage.header.vietnam') }}
                                        </a>
                                    </div>
                                </li>
                                <li @click.prevent="changeLanguage('en')">
                                    <div class="author-thumb">
                                        <img src="/images/en.png" alt="author">
                                    </div>
                                    <div class="notification-event">
                                        <a href="javascript:void(0)" class="h6 notification-friend">
                                            {{ $t('homepage.header.english') }}
                                        </a>
                                    </div>
                                </li>
                                <li @click.prevent="changeLanguage('ja')">
                                    <div class="author-thumb">
                                        <img src="/images/jp5.png" alt="author">
                                    </div>
                                    <div class="notification-event">
                                        <a href="javascript:void(0)" class="h6 notification-friend">
                                            {{ $t('homepage.header.japan') }}
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--End: control-block -->
            <div class="control-log control-block" v-else>
                <router-link to="/login">{{ $t('homepage.header.login') }}</router-link> |
                <router-link to="/register">{{ $t('homepage.header.register') }}</router-link>
            </div>
        </div>

        <action-detail
            :showAction.sync="showAction"
            :dataAction.sync="dataAction.list_action"
            :checkLikeActions.sync="dataAction.checkLikeAction"
            :canComment.sync="checkPermission">
        </action-detail>
    </header>
</template>

<script>
import ActionDetail from '../../event/ActionDetail.vue'
import { mapState, mapActions } from 'vuex'
import {
    logout,
    showNotification,
    getListRequest,
    rejectRequest,
    markRead,
    acceptRequest
} from '../../../router/router'
import { post, get } from '../../../helpers/api'
import noty from '../../../helpers/noty'
import Notification from './Notification'
import { EventBus } from '../../../EventBus.js'

export default {
    data: () => ({
        messages: [],
        paginate: 0,
        continue: true,
        listRequest: [],
        count: 0,
        skipFriend: 0,
        continueForGetListRequest: true,
        countReadMessage: 0,
        keyword: '',
        usersFinded: [],
        campaignsFinded: [],
        lang: '',
        /*--- list Notification ---*/
        listNotification: [],
        totalUnreadNotifications: 0,
        page: 1,
        totalPage: 1,
        loading: false,
        show: false,
        showFriend: false,
        showMessage: false,
        showAction: false,
        dataAction: [],
        checkPermission: true
    }),
    created () {
        this.lang = !!localStorage.getItem('locale') ? localStorage.getItem('locale') : window.Laravel.locale
        EventBus.$on('redirect-page', () => {
            this.keyword = ''
            this.search()
        })

        if (this.authenticated) {
            this.getTotalUnreadNotification()

            EventBus.$on('getListFriends', () => {
                this.getMessagesNotification()
            })

            this.getListRequest()
            EventBus.$on('markRead', data => {
                let index = this.messages.findIndex(mess => parseInt(mess.to) == parseInt(data.receiveId)
                    && parseInt(mess.from) == parseInt(data.senderId))

                if (index == -1) {
                    return
                }

                this.countReadMessage = this.messages[index].read || this.countReadMessage == 0
                    ? this.countReadMessage
                    : this.countReadMessage - 1
                this.messages[index].read = true
                this.messages[index].class = ''
            })
        }

        EventBus.$on('seen', () => {
            this.markReadNotifications()
        })
    },
    computed: {
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user,
            groups: state => state.groups,
            friends: state => state.listContact
        })
    },
    mounted() {
        const vm = this
        $('#notification_messages').on('scroll', function() {
            if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
                vm.getMessagesNotification()
            }
        })

        $(document).on('click', function(e) {
            if (e.target.id != 'detail-notification' && vm.show && !$('#detail-notification').find(e.target).length) {
               vm.show = false
               vm.markReadNotifications()
            } else if (e.target.id != 'friend-suggest' && vm.showFriend && !$('#friend-suggest').find(e.target).length) {
                vm.showFriend = false
            } else if (e.target.id != 'show-messages' && vm.showMessage && !$('#show-messages').find(e.target).length) {
                vm.showMessage = false
            }
        })

        $('.ul-notification').on('scroll', function() {
            if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
                vm.loadMoreNotification()
            }
        })
    },
    beforeDestroy() {
        $(window).off()
    },
    methods: {
        ...mapActions('auth', [
            'logout',
            'getListFollow'
        ]),
        handleLogout() {
            post(logout).then(res => {
                this.logout()
                this.$router.push('/login')
            }).catch(err => {
                this.$router.push('/')
            })
        },

        /*--- List Notification ---*/
        getTotalUnreadNotification() {
            get('total-unread-notifications')
                .then(res => {
                    this.totalUnreadNotifications = res.data.totalUnread
                })
                .catch(err => {
                    noty({
                        text: this.$i18n.t('messages.error'),
                        type: 'error',
                        force: false,
                        container: false
                    })
                })
        },

        getListNotification() {
            if (!this.show) {
                this.showMessage = false
                this.showFriend = false

                if (!this.listNotification.length || this.totalUnreadNotifications) {
                    this.loading = true
                    get('list-notification')
                        .then(res => {
                            this.listNotification = res.data.notifications.data
                            this.totalPage = res.data.notifications.last_page
                            this.loading = false
                        })
                        .catch(err => {
                            this.loading = false
                            noty({
                                text: this.$i18n.t('messages.error'),
                                type: 'error',
                                force: false,
                                container: false
                            })
                        })
                }
            } else {
                this.markReadNotifications()
            }

            this.show = !this.show
        },

        loadMoreNotification() {
            if (this.page < this.totalPage) {
                this.loading = true
                get(`list-notification?page=${++this.page}`)
                    .then(res => {
                        this.listNotification = this.listNotification.concat(res.data.notifications.data)
                        this.loading = false
                    })
                    .catch(err => {
                        this.loading = false
                        noty({
                            text: this.$i18n.t('messages.error'),
                            type: 'error',
                            force: false,
                            container: false
                        })
                    })
            }
        },

        markReadNotifications() {
            post('mark-read-notifications')
                .then(res => {
                    this.totalUnreadNotifications = 0
                    this.listNotification.forEach((item, index) => {
                        this.listNotification[index].read_at = new Date().toJSON().slice(0, 10).replace(/-/g, '/')
                    })
                })
                .catch(err => {
                    noty({
                        text: this.$i18n.t('messages.error'),
                        type: 'error',
                        force: false,
                        container: false
                    })
                })
        },

        /*--- List message ---*/
        showChatMessages() {
            this.showMessage = !this.showMessage
            this.show = false
            this.showFriend = false
        },
        getMessagesNotification() {
            if (this.continue) {
                get(`${showNotification}?paginate=${this.paginate}`)
                    .then(res => {
                        if (res.data.status == 200) {
                            let noty = res.data.notifications
                            var self = this

                            for (var index = 0; index < noty.length; index++) {
                                (function () {
                                    var isSendToUser = Number.isInteger(Number(noty[index].content.receive))
                                    var mess = {
                                        from: noty[index].content.userId,
                                        sendName: self.$i18n.t('messages.you'),
                                        to: noty[index].content.receive,
                                        groupChat: noty[index].content.groupKey,
                                        message: noty[index].content.message,
                                        showName: noty[index].content.nameReceive,
                                        showAvatar: noty[index].content.avatarReceive,
                                        class: isSendToUser
                                            ? (noty[index].isRead || self.user.id == noty[index].content.userId ? "" : "message-unread")
                                            : "group-chat",
                                        time: noty[index].isRead ? noty[index].time : noty[index].content.time,
                                        read: noty[index].content.userId == self.user.id ? true : noty[index].isRead
                                    }

                                    self.countReadMessage = !mess.read && mess.read != null
                                        ? (self.countReadMessage + 1)
                                        : self.countReadMessage

                                    self.messages.push(mess)
                                })();
                            }

                            this.paginate = res.data.paginate
                        }

                        this.continue = res.data.continue
                        this.revertMessage()
                    })
                    .catch(err => {
                        const message = this.$i18n.t('messages.load_notification_message_fail')
                        noty({ text: message, container: false, force: true})
                    })
            }
        },
        receiveMessage(data, option) {
            var socketData = JSON.parse(data)

            if (socketData.success ) {
                var message = JSON.parse(socketData.message)
                let index = this.messages.findIndex(mess => mess.groupChat == socketData.groupChat)

                let mess = {
                    from: socketData.from,
                    sendName: (socketData.from == this.user.id)
                        ? this.$i18n.t('messages.you')
                        : socketData.name + ": ",
                    to: socketData.to,
                    groupChat: socketData.groupChat,
                    message: message.message,
                    showName: (socketData.from == this.user.id || !option)
                        ? message.nameReceive
                        : message.name,
                    showAvatar: (socketData.from == this.user.id || !option)
                        ? message.avatarReceive
                        : message.avatar,
                    class: socketData.from != this.user.id
                        ? "message-unread" : "group-chat",
                    time: message.time,
                    read: false
                }

                if (index == -1) {
                    this.messages.unshift(mess)

                    if (this.user.id != Number(mess.from)) {
                        this.countReadMessage += 1
                    }
                } else {
                    this.countReadMessage = !this.messages[index].read || socketData.from == this.user.id
                        ? this.countReadMessage
                        : this.countReadMessage + 1
                    this.messages.splice(index, 1)
                    this.messages.unshift(mess)
                }

                this.paginate++
            }
        },
        calendarTime(time) {
            return moment(time).calendar()
        },
        getListRequest() {
            if (this.continueForGetListRequest) {
                get(`${getListRequest}?skip=${this.skipFriend}`)
                    .then(res => {
                        let data = res.data.notifications

                        for (var index = 0; index < data.length; index++) {
                            let user = {
                                id: data[index].id,
                                userId: data[index].data.form.id,
                                userName: data[index].data.form.name,
                                avatar: data[index].data.form.image_small,
                                accept: false
                            }

                            this.listRequest.push(user)
                        }

                        this.count = res.data.unread
                        this.skipFriend = res.data.skip
                        this.continueForGetListRequest = res.data.continue
                    })
                    .catch(err => {
                        const message = this.$i18n.t('messages.connection_error')
                        noty({ text: message, container: false, force: true })
                    })
            }
        },
        acceptRequest(id, userId) {
            post(`${acceptRequest}/${userId}`, { id: id })
                .then(res => {
                    let index = this.listRequest.findIndex(request => request.userId == userId)

                    if (index != -1) {
                        this.$socket.emit('acceptRequest', {
                            userId: userId,
                            acceptId: this.user.id,
                            avatar: this.user.image_thumbnail,
                            name: this.user.name,
                            receiveAvatar: this.listRequest[index].avatar,
                            receiveName: this.listRequest[index].userName
                        })

                        this.listRequest.splice(index, 1)
                    }
                })
        },
        rejectRequest(id, userId) {
            post(rejectRequest, { id: id, userId: userId })
                .then(res => {
                    if (res.data.http_status.code == 200) {
                        this.count = (this.count > 0) ? this.count - 1 : this.count
                        let index = this.listRequest.findIndex(request => request.id == id)
                        this.$socket.emit('rejectRequest', {
                            userId: this.user.id,
                            rejectId: userId,
                            index: index
                        })
                    }
                })
                .catch(err => {
                    const message = this.$i18n.t('messages.connection_error')
                    noty({ text: message, container: false, force: true })
                })
        },
        markRead(type) {
            this.showFriend = !this.showFriend
            this.showMessage = false
            this.show = false

            if (this.count) {
                post(markRead, { type: type })
                    .then(res => {
                        if (res.data.http_status.code == 200) {
                            this.count = res.data.unread
                        }
                    })
                    .catch(err => {
                        const message = this.$i18n.t('messages.connection_error')
                        noty({ text: message, container: false, force: true })
                    })
            }
        },
        searchRedirect() {
            this.$router.push({ name: 'search', params: { keyword: this.keyword }})
            this.keyword = ''
            this.search()
        },
        search: _.debounce(function () {
            if (this.keyword.trim()) {
                // 1 is page
                // 3 is the amount of data retrieved
                // all is type which gets all data
                get(`search/1/3/all?keyword=${this.keyword}`)
                    .then(res => {
                        this.usersFinded = res.data.users
                        this.campaignsFinded = res.data.campaigns
                    })
                    .catch(err => {
                        noty({
                            text: this.$i18n.t('messages.connection_error'),
                            container: false,
                            force: true
                        })
                    })
            } else {
                 this.usersFinded = this.campaignsFinded = []
            }
        }, 500),
        revertMessage() {
            for (let i = 0; i < this.messages.length; i++) {
                let index = this.friends
                    .findIndex(user => user.id == Number(this.messages[i].to)
                        || user.id == Number(this.messages[i].from))

                if (index != -1) {
                    this.messages[i].showName = this.friends[index].name
                    this.messages[i].showAvatar = this.friends[index].image_small

                    if (Number(this.messages[i].from) != this.user.id) {
                        this.messages[i].sendName = this.friends[index].name + ' :'
                    }
                }
            }
        },
        addChatComponent(mess) {
            if (!Number(mess.to) && typeof mess.to === 'string' || !Number(mess.from) && typeof mess.from === 'string') {
                let campaign = this.groups.filter(group => {
                    return group.hashtag === mess.showName
                })

                if (!campaign.length) {
                    return
                }

                EventBus.$emit('addChatComponent', {
                    id: campaign[0].hashtag,
                    name: campaign[0].hashtag,
                    singleChat: false,
                    slug: campaign[0].id
                })
            } else {
                let id = (Number(mess.to) != this.user.id)
                    ? Number(mess.to) : Number(mess.from)
                EventBus.$emit('addChatComponent', {
                    id: id,
                    name: mess.showName,
                    singleChat: true,
                    slug: id
                })
            }
        },
        changeLanguage(locale) {
            this.$i18n.locale = this.lang = locale
            this.$validator.setLocale(locale)
            window.moment.locale(locale)
            localStorage.setItem('locale', locale)
            EventBus.$emit('changeLanguage', { locale: locale })
        }
    },
    components: {
        Notification,
        ActionDetail
    },

    sockets: {
        singleChat: function (data) {
            this.receiveMessage(data, true)
        },
        groupChat: function (data) {
            this.receiveMessage(data, false)
        },
        noty: function (data) {
            data = JSON.parse(data)

            if (data.type) {
                let user = {
                    id: null,
                    userId: data.noty.id,
                    userName: data.noty.name,
                    avatar: data.noty.image_small
                }

                this.listRequest.unshift(user)
                this.count += 1
            } else {
                let index = this.listRequest.findIndex(user => user.userId == data.noty.id)

                if (index != -1) {
                    this.listRequest.splice(index, 1)
                    this.count -= 1
                }
            }
        },
        acceptRequestSuccess: function (data) {
            if (data.status) {
                this.getListFollow()
                let socketData = data.data
                let mess = {
                    id: null,
                    avatar: (socketData.acceptId == this.user.id)
                        ? socketData.receiveAvatar
                        : socketData.avatar,
                    userName: (socketData.acceptId == this.user.id)
                        ? socketData.receiveName
                        : socketData.name,
                    accept: true
                }

                let index = this.listRequest.findIndex(request => request.userId == data.data.userId)

                if (index != -1) {
                    this.listRequest.splice(index, 1)
                    this.count = this.count > 0 ? this.count - 1 : this.count
                }

                this.listRequest.unshift(mess)
            }
        },
        rejectRequestSuccess: function (data) {
            if (data.data.index != -1) {
                this.listRequest.splice(data.data.index, 1)
            } else {
                if (this.user.id == data.data.userId) {
                    let index = this.listRequest.findIndex(re => re.userId === data.data.rejectId)

                    if (index == -1) {
                        return
                    }

                    this.listRequest.splice(index, 1)
                }
            }

            this.count = (this.count > 0) ? this.count - 1 : this.count
        },

        //--- List Notification ---//
        getNotification: function (notification) {
            this.totalUnreadNotifications++
            if (this.show) {
                this.listNotification.unshift(notification.data)
            }
        }
    }
}
</script>

<style lang="scss">
 #notification_messages, #notification_list_request, ul {
    &::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        background-color: #F5F5F5;
    }

    &::-webkit-scrollbar {
        width: 2px;
        background-color: #ff5e3a;
    }

    &::-webkit-scrollbar-thumb {
        background-color: #494c62;
    }
}

.div-notification, .div-friend, .div-messages {
    .ul-notification {
        overflow-y: scroll;
        max-height: 300px;

        .un-read {
            background: rgba(246, 247, 243, 0.82);
            &:hover {
                background: #fafbfd;
            }
        }
    }

    &.more{
        b {
            color: #515365
        }

        .more-dropdown {
            visibility: visible;
            opacity: 1;
            padding: 0 0 40px 0;
        }
    }

    .author-thumb {
        img {
            height: 34px;
            width: 34px;
        }
    }

    .li-loading {
        text-align: center;
        font-size: 6px;
        padding: 10px 0px 10px;
        border: 0px;

        span {
            font-size: 12px;
        }
    }

    .notification-empty {
        text-align: center;
        font-weight: bold;
        i {
            margin-top: 0px;
            margin-right: 2px;
            font-size: 20px;
        }
    }
}

.select-lang {
    width: 95px;
    color: white;
    font-weight: bold;
    font-size: 15px;
    margin-left: 20px;
    margin-right: -40px;

    >img {
        width: 23px;
        height: 16px;
    }

    &.control-icon {
        .more-lang {
            right: -25px;
            width: 130px;
            padding: 0;;
            box-shadow: 0 0 34px 0 rgba(192, 194, 204, 0.55);

            .ui-block-title.ui-block-title-small {
                padding: 7px;
                text-align: center;
            }

            .notification-list {
                .notification-event {
                    padding-left: 0px;
                    a {
                        &:hover {
                            color: #515365;
                        }
                    }
                }

            .notification-friend {
                    margin-right: 0px;
                    padding: 5px 0px 0px 0px;
                    font-size: 12px;
                }
                li {
                    padding: 5px 10px;
                    border-bottom: 1px solid #e6ecf5;
                    display: block;
                    position: relative;
                    transition: all .3s ease;
                    .author-thumb {
                        height: 27px;
                        width: 27px;
                        img {
                            width: 23px;
                            height: 18px;
                            border-radius: 0px;
                            box-shadow: 2px 1px 5px -1px #404358;
                        }
                    }

                    &:last-child {
                        border-bottom: 0px;
                    }
                }
            }
        }
    }
}

.author-thumb {
    .avatar {
        width: 40px;
        height: 40px;
    }
}

.hashtag {
    margin: 1px 0px;
}

.result-campaign {
    padding: 13px 25px !important;
}

.tag-info {
    padding: 2px 7px;
    color: white;
    margin: auto 1px;
    border-radius: 4px;
    background: #57b6ff;
    font-weight: bold;
}

#notification_messages, #notification_list_request {
    overflow-y: scroll !important;
}

.search-bar {
    .selectize-dropdown {
        display: block;
        width: 500px;
        top: 70px;
        left: 0px;
        visibility: visible;
        border: none;
    }
    .form-group.with-button {
        input {
            padding: 0px 15px;
        }
    }
    .img-campaign {
        width: 100%;
        height: 100%;
    }
}

.control-log {
    text-transform: uppercase;
    font-weight: bold;
    a {
        padding: 0px 3px;
        color: #08ddc1;
    }
}

#img-author-showAvatar {
    height: 34px;
    width: 34px;
}

.page-title {
    a {
        color: #fff;
    }
}

.message-unread {
    background-color: rgb(236, 239, 241) !important;
}

.model-action {
    z-index: 23;
}
</style>
