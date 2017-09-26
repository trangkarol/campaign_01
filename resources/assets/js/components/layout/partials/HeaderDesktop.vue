<template lang="html">
    <header class="header" id="site-header">
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
                <div class="control-icon more has-items" @click="markRead(0)">
                    <svg class="olymp-happy-face-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                    </svg>
                    <div class="label-avatar bg-blue" v-show="count">{{ count }}</div>
                    <div class="more-dropdown more-with-triangle triangle-top-center">
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
                                        <a href="#" class="h6 notification-friend">{{ request.userName }}</a>
                                    </div>
                                    <div class="notification-event" v-else>
                                        {{ $t('messages.you_and') }}<a href="#" class="h6 notification-friend">{{ request.userName }}</a>{{ $t('messages.became_friend') }}<a href="#" class="notification-link"></a>.
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
                            </ul>
                        </div>
                        <a href="javascript:void(0)" @click="getListRequest()" class="view-all bg-blue" v-show="count">{{ $t('messages.show_more') }}</a>
                    </div>
                </div>
                <div class="control-icon more has-items">
                    <svg class="olymp-chat---messages-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-chat---messages-icon"></use>
                    </svg>
                    <div class="label-avatar bg-purple" v-show="countReadMessage">{{ countReadMessage }}</div>
                    <div class="more-dropdown more-with-triangle triangle-top-center">
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
                                            <time class="entry-date updated" datetime="2004-07-24T18:18" v-if="mess.read">
                                                {{ $t('homepage.header.readed_at') + ' ' + calendarTime(mess.time) }}
                                            </time>
                                            <time class="entry-date updated" datetime="2004-07-24T18:18" v-else>
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

                            </ul>
                        </div>
                        <a href="javascript:void(0)" class="view-all bg-purple" @click="getMessagesNotification">
                            {{ $t('messages.more_messages') }}
                        </a>
                    </div>
                </div>
                <div class="control-icon more has-items" style="display:none;">
                    <svg class="olymp-thunder-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-thunder-icon"></use>
                    </svg>
                    <div class="label-avatar bg-primary">8</div>
                    <div class="more-dropdown more-with-triangle triangle-top-center">
                        <div class="ui-block-title ui-block-title-small">
                            <h6 class="title">Notifications</h6>
                            <a href="#">Mark all as read</a>
                            <a href="#">Settings</a>
                        </div>
                        <div class="mCustomScrollbar" data-mcs-theme="dark">
                            <ul class="notification-list">
                                <li>
                                    <div class="author-thumb">
                                        <img src="/images/avatar62-sm.jpg" alt="author">
                                    </div>
                                    <div class="notification-event">
                                        <div>
                                            <a href="#" class="h6 notification-friend">Mathilda Brinker</a> commented on your new
                                            <a href="#" class="notification-link">profile status</a>.</div>
                                        <span class="notification-date">
                                            <time class="entry-date updated" datetime="2004-07-24T18:18">4 hours ago</time>
                                        </span>
                                    </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-comments-post-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-comments-post-icon"></use>
                                        </svg>
                                    </span>
                                    <div class="more">
                                        <svg class="olymp-three-dots-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                        </svg>
                                        <svg class="olymp-little-delete">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-little-delete"></use>
                                        </svg>
                                    </div>
                                </li>
                                <li class="un-read">
                                    <div class="author-thumb">
                                        <img src="/images/avatar63-sm.jpg" alt="author">
                                    </div>
                                    <div class="notification-event">
                                        <div>You and
                                            <a href="#" class="h6 notification-friend">Nicholas Grissom</a> just became friends. Write on
                                            <a href="#" class="notification-link">his wall</a>.</div>
                                        <span class="notification-date">
                                            <time class="entry-date updated" datetime="2004-07-24T18:18">9 hours ago</time>
                                        </span>
                                    </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-happy-face-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                                        </svg>
                                    </span>
                                    <div class="more">
                                        <svg class="olymp-three-dots-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                        </svg>
                                        <svg class="olymp-little-delete">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-little-delete"></use>
                                        </svg>
                                    </div>
                                </li>
                                <li class="with-comment-photo">
                                    <div class="author-thumb">
                                        <img src="/images/avatar64-sm.jpg" alt="author">
                                    </div>
                                    <div class="notification-event">
                                        <div>
                                            <a href="#" class="h6 notification-friend">Sarah Hetfield</a> commented on your
                                            <a href="#" class="notification-link">photo</a>.</div>
                                        <span class="notification-date">
                                            <time class="entry-date updated" datetime="2004-07-24T18:18">Yesterday at 5:32am</time>
                                        </span>
                                    </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-comments-post-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-comments-post-icon"></use>
                                        </svg>
                                    </span>
                                    <div class="comment-photo">
                                        <img src={{ asset('images/comment-photo1.jpg') }} alt="photo">
                                        <span>“She looks incredible in that outfit! We should see each...”</span>
                                    </div>
                                    <div class="more">
                                        <svg class="olymp-three-dots-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                        </svg>
                                        <svg class="olymp-little-delete">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-little-delete"></use>
                                        </svg>
                                    </div>
                                </li>
                                <li>
                                    <div class="author-thumb">
                                        <img src="/images/avatar65-sm.jpg" alt="author">
                                    </div>
                                    <div class="notification-event">
                                        <div>
                                            <a href="#" class="h6 notification-friend">Green Goo Rock</a> invited you to attend to his event Goo in
                                            <a href="#" class="notification-link">Gotham Bar</a>.</div>
                                        <span class="notification-date">
                                            <time class="entry-date updated" datetime="2004-07-24T18:18">March 5th at 6:43pm</time>
                                        </span>
                                    </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-happy-face-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                                        </svg>
                                    </span>
                                    <div class="more">
                                        <svg class="olymp-three-dots-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                        </svg>
                                        <svg class="olymp-little-delete">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-little-delete"></use>
                                        </svg>
                                    </div>
                                </li>
                                <li>
                                    <div class="author-thumb">
                                        <img src="/images/avatar66-sm.jpg" alt="author">
                                    </div>
                                    <div class="notification-event">
                                        <div>
                                            <a href="#" class="h6 notification-friend">James Summers</a> commented on your new
                                            <a href="#" class="notification-link">profile status</a>.</div>
                                        <span class="notification-date">
                                            <time class="entry-date updated" datetime="2004-07-24T18:18">March 2nd at 8:29pm</time>
                                        </span>
                                    </div>
                                    <span class="notification-icon">
                                        <svg class="olymp-heart-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use>
                                        </svg>
                                    </span>
                                    <div class="more">
                                        <svg class="olymp-three-dots-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                        </svg>
                                        <svg class="olymp-little-delete">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-little-delete"></use>
                                        </svg>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <a href="#" class="view-all bg-primary">View All Notifications</a>
                    </div>
                </div>
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
                <div class="select-lang control-icon more has-items">
                    <img src="/images/vn.png" v-show="lang == 'vi'" alt="author">
                    <img src="/images/jp5.png" v-show="lang == 'ja'" alt="author">
                    <img src="/images/en.png" v-show="lang == 'en'" alt="author">
                    {{ $t('homepage.header.language') }}
                    <div class="more-lang more-dropdown more-with-triangle triangle-top-center">
                        <div class="ui-block-title ui-block-title-small">
                            <h6 class="title">{{ $t('homepage.header.choose_language') }}</h6>
                        </div>
                        <div class="mCustomScrollbar ps ps--theme_default ps--active-y" data-mcs-theme="dark" data-ps-id="a157eff2-42d1-dbd0-3583-bc5f938692d2">
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
    </header>
</template>

<script>
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
        lang: ''
    }),
    created () {
        this.lang = !!localStorage.getItem('locale') ? localStorage.getItem('locale') : window.Laravel.locale
        EventBus.$on('redirect-page', () => {
            this.keyword = ''
            this.search()
        })

        if (this.authenticated) {
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
    },
    computed: {
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user,
            groups: state => state.groups,
            friends: state => state.listContact
        })
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
                    slug: this.replaceSpace(campaign[0].title + '-' + campaign[0].id)
                })
            } else {
                let id = (Number(mess.to) != this.user.id)
                    ? Number(mess.to) : Number(mess.from)
                EventBus.$emit('addChatComponent', {
                    id: id,
                    name: mess.showName,
                    singleChat: true,
                    slug: this.replaceSpace(mess.showName + '-' + id)
                })
            }
        },
        replaceSpace(str) {
            return str.replace(' ', '-').toLowerCase();
        },
        changeLanguage(locale) {
            this.$i18n.locale = this.lang = locale
            this.$validator.setLocale(locale)
            window.moment.locale(locale)
            localStorage.setItem('locale', locale)
        }
    },
    mounted() {
        const vm = this
        $('#notification_messages').on('scroll', function() {
            if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
                vm.getMessagesNotification()
            }
        })
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
        }
    }
}
</script>

<style lang="scss">
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

#chat-notification {
    color: #0c0c0c !important;
    p {
        color: #0c0c0c !important;
    }
}
</style>
