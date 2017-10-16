<template>
    <!-- notification when user invited to join campaign -->
    <li :class="{ 'un-read': !notification.read_at }"
        @click="redirect(notification)"
        v-if="notification.type == 'App\\Notifications\\InviteUser'">
        <div class="author-thumb">
            <img :src="notification.data.from.image_thumbnail" alt="author">
        </div>
        <div class="notification-event">
            <div>
                <b>{{ notification.data.from.name }}</b>
                {{ $t('homepage.header.invited_join') }}
                <b class="title-of-campaign">{{ notification.data.campaign.title }}</b>.
            </div>
            <span class="notification-date">
                <time class="entry-date updated">{{ timeAgo(notification.created_at) }}</time>
            </span>
        </div>
        <span class="notification-icon">
            <svg class="olymp-happy-face-icon">
                <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
            </svg>
        </span>
    </li>

    <!-- notification when user invited to join campaign -->
    <li :class="{ 'un-read': !notification.read_at }"
        @click="redirect(notification)"
        v-else-if="notification.type == 'App\\Notifications\\UserDonate'">
        <div class="author-thumb">
            <img :src="notification.data.from.image_thumbnail" alt="author">
        </div>
        <div class="notification-event">
            <div>
                <b>{{ notification.data.from.name }}</b>
                {{ $t('homepage.header.user_donated') }}
                <b class="title-of-event">{{ notification.data.event.title }}</b>.
            </div>
            <span class="notification-date">
                <time class="entry-date updated">{{ timeAgo(notification.created_at) }}</time>
            </span>
        </div>
        <span class="notification-icon">
            <i class="fa fa-gift" aria-hidden="true"></i>
        </span>
    </li>

    <!-- notification when owner event accept donation -->
    <li :class="{ 'un-read': !notification.read_at }"
        @click="redirect(notification)"
        v-else-if="notification.type == 'App\\Notifications\\AcceptDonation'">
        <div class="author-thumb">
            <img :src="notification.data.from.image_thumbnail" alt="author">
        </div>
        <div class="notification-event">
            <div>
                <b>{{ notification.data.from.name }}</b>
                {{ $t('homepage.header.accept_donate') }}
                <b class="title-of-event">{{ notification.data.event.title }}</b>.
            </div>
            <span class="notification-date">
                <time class="entry-date updated">{{ timeAgo(notification.created_at) }}</time>
            </span>
        </div>
        <span class="notification-icon">
            <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
        </span>
    </li>

    <!-- notification when someone request to join campaign -->
    <li :class="{ 'un-read': !notification.read_at }"
        @click="redirect(notification)"
        v-else-if="notification.type == 'App\\Notifications\\UserRequest'">
        <div class="author-thumb">
            <img :src="notification.data.from.image_thumbnail" alt="author">
        </div>
        <div class="notification-event">
            <div>
                <b>{{ notification.data.from.name }}</b>
                {{ $t('homepage.header.request_join') }}
                <b class="title-of-campaign">{{ notification.data.campaign.title }}</b>.
            </div>
            <span class="notification-date">
                <time class="entry-date updated">{{ timeAgo(notification.created_at) }}</time>
            </span>
        </div>
        <span class="notification-icon">
            <i class="fa fa-reply" aria-hidden="true"></i>
        </span>
    </li>

    <!-- notification when Owner campaign accept request's user -->
    <li :class="{ 'un-read': !notification.read_at }"
        @click="redirect(notification)"
        v-else-if="notification.type == 'App\\Notifications\\AcceptRequest'">
        <div class="author-thumb">
            <img :src="notification.data.from.image_thumbnail" alt="author">
        </div>
        <div class="notification-event">
            <div>
                <b>{{ notification.data.from.name }}</b>
                {{ $t('homepage.header.accept_request') }}
                <b class="title-of-campaign">{{ notification.data.campaign.title }}</b>.
            </div>
            <span class="notification-date">
                <time class="entry-date updated">{{ timeAgo(notification.created_at) }}</time>
            </span>
        </div>
        <span class="notification-icon">
            <i class="fa fa-check-square-o" aria-hidden="true"></i>
        </span>
    </li>

    <!-- notification when user create action -->
    <li :class="{ 'un-read': !notification.read_at }"
        @click="redirect(notification)"
        v-else-if="notification.type == 'App\\Notifications\\CreateAction'">
        <div class="author-thumb">
            <img :src="notification.data.from.image_thumbnail" alt="author">
        </div>
        <div class="notification-event">
            <div>
                <b>{{ notification.data.from.name }}</b>
                {{ $t('homepage.header.create_action') }}
                <b class="title-of-event">{{ notification.data.event.title }}</b>.
            </div>
            <span class="notification-date">
                <time class="entry-date updated">{{ timeAgo(notification.created_at) }}</time>
            </span>
        </div>
        <span class="notification-icon">
            <img src="\images\action.png" alt="action" class="img-action">
        </span>
    </li>

    <!-- notification when user create event -->
    <li :class="{ 'un-read': !notification.read_at }"
        @click="redirect(notification)"
        v-else-if="notification.type == 'App\\Notifications\\CreateEvent'">
        <div class="author-thumb">
            <img :src="notification.data.from.image_thumbnail" alt="author">
        </div>
        <div class="notification-event">
            <div>
                <b>{{ notification.data.from.name }}</b>
                {{ $t('homepage.header.create_event') }}
                <b class="title-of-event">{{ notification.data.event.title }}</b>.
            </div>
            <span class="notification-date">
                <time class="entry-date updated">{{ timeAgo(notification.created_at) }}</time>
            </span>
        </div>
        <span class="notification-icon">
            <i aria-hidden="true" class="fa fa-calendar-check-o"></i>
        </span>
    </li>

    <!-- notification when user comment -->
    <li :class="{ 'un-read': !notification.read_at }"
        @click="redirect(notification)"
        v-else-if="notification.type == 'App\\Notifications\\UserComment'">
        <div class="author-thumb">
            <img :src="notification.data.from.image_thumbnail" alt="author">
        </div>
        <div class="notification-event">
            <div>
                <b>{{ notification.data.from.name }}</b>
                <span v-if="notification.data.comment.parent_id">
                    {{ $t('homepage.header.replied_comment') }}
                </span>
                <span v-else>{{ $t('homepage.header.commented') }}</span>
                <span v-if="notification.data.comment.commentable_type == 'App\\Models\\Campaign'">
                    {{ $t('homepage.header.campaign') }}
                    <b class="title-of-event">
                        {{ notification.data.comment.commentable.title }}
                    </b>
                </span>
                <span v-else-if="notification.data.comment.commentable_type == 'App\\Models\\Event'">
                    {{ $t('homepage.header.event') }}
                    <b class="title-of-event">
                        {{ notification.data.comment.commentable.title }}
                    </b>
                </span>
                <span v-else>
                    {{ $t('homepage.header.action') }}
                    <b class="title-of-event">
                        {{ notification.data.comment.commentable.caption }}
                    </b>
                </span>
            </div>
            <span class="notification-date">
                <time class="entry-date updated">{{ timeAgo(notification.created_at) }}</time>
            </span>
        </div>
        <span class="notification-icon">
            <i v-if="notification.data.comment.parent_id" aria-hidden="true" class="fa fa-reply-all"></i>
            <svg class="olymp-speech-balloon-icon" v-else>
                <use xlink:href="/frontend/icons/icons.svg#olymp-speech-balloon-icon"></use>
            </svg>
        </span>
        <div class="comment-photo">
            <img :src="notification.data.from.image_thumbnail" alt="photo">
            <span>“{{ notification.data.comment.content }}”</span>
        </div>
    </li>

</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import ActionDetail from '../../event/ActionDetail.vue'

    export default {
        data: () => ({

        }),
        props: {
            notification: {},
            totalUnreadNotifications: 0,
            show: false,
            showAction: {},
            dataAction: {},
            checkLikeActions: {},
            checkPermission: true
        },
        computed: {
            ...mapState('auth', {
                user: state => state.user,
            })
        },
        components : {
            ActionDetail,
        },
        methods: {
            ...mapActions('action', [
                'getDetailAction',
            ]),
            timeAgo(time) {
                return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
            },
            detailAction(actionId) {
                this.getDetailAction(actionId)
                .then(data => {
                    this.$emit('update:showAction', true)
                    this.$emit('update:dataAction', data.actions.list_action)
                    this.$emit('update:checkPermission', data.checkPermission)
                    this.$emit('update:checkLikeActions', data.actions.checkLikeAction)
                })
                .catch(err => {
                    this.$emit('update:showAction', false)
                    const message = this.$i18n.t('messages.message-fail')
                    noty({ text: message, force: true, container: false })
                })
            },

            redirect(notification) {
                switch (notification.type) {
                    case 'App\\Notifications\\InviteUser':
                    case 'App\\Notifications\\AcceptRequest':
                        this.$router.push({ name: 'campaign.timeline', params: {
                            slug: notification.data.campaign.slug
                        }})
                        break
                    case 'App\\Notifications\\UserRequest':
                        this.$router.push({ name: 'campaign.member_request', params: {
                            slug: notification.data.campaign.slug
                        }})
                        break
                    case 'App\\Notifications\\UserDonate':
                    case 'App\\Notifications\\AcceptDonation':
                        this.$router.push({ name: 'event.donation', params: {
                            slug: notification.data.event.campaign_id,
                            slugEvent: notification.data.event.slug
                        }})
                        break
                    case 'App\\Notifications\\CreateAction':
                    case 'App\\Notifications\\CreateEvent':
                        this.$router.push({ name: 'event.index', params: {
                            slug: notification.data.event.campaign_id,
                            slugEvent: notification.data.event.slug
                        }})
                        break
                    case 'App\\Notifications\\UserComment':
                        if (notification.data.comment.commentable_type == 'App\\Models\\Campaign') {
                            this.$router.push({ name: 'user.timeline', params: { slug: this.user.slug }})
                        } else if (notification.data.comment.commentable_type == 'App\\Models\\Event') {
                            this.$router.push({ name: 'event.index', params: {
                                slug: notification.data.comment.commentable.campaign_id,
                                slugEvent: notification.data.comment.commentable.slug
                            }})
                        } else {
                            this.detailAction(notification.data.comment.commentable.id)
                        }
                        break
                    default: this.$router.push({ name: 'homepage' })
                }

                this.$emit('update:totalUnreadNotifications', 0)
                this.$emit('update:show', false)
            }
        }
    }
</script>

<style lang="scss" scoped>
    .un-read {
        background: rgba(246, 247, 243, 0.82);
        &:hover {
            background: #fafbfd;
        }
    }

    i {
        font-size: 25px;
    }

    .fa-gift {
        color: rgb(121, 124, 148);
    }

    .fa-thumbs-o-up{
        color: #55c1b4;
    }

    .fa-reply {
        color: #5bc2ba;
    }

    .fa-check-square-o {
        color: #ffa58f;
    }

    .fa-calendar-check-o {
        color: #4a4d62;
    }

    .fa-reply-all {
        color: #8fb7b1;
    }

    .title-of-event {
        color: #ff5e3a !important;
    }

    .title-of-campaign {
        color: #4abda7 !important;
    }

    .img-action {
        margin-right: -5px;
        width: 25px;
        height: 25px;
    }

    .olymp-speech-balloon-icon {
        fill: rgb(175, 202, 198);
    }

    .comment-photo {
        img {
            height: 30px;
            width: 30px;
        }
    }
</style>
