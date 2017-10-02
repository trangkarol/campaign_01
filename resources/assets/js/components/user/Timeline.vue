<template>
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-xl-6 push-xl-3 col-lg-12 push-lg-0 col-md-12 col-sm-12 col-xs-12" v-if="inforListActivity">
                <div class="page-description" v-if="!inforListActivity.total">
                    <div class="icon">
                        <svg class="olymp-star-icon left-menu-icon">
                            <use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use>
                        </svg>
                    </div>
                    <span>{{ $t('user.activity_empty') }}</span>
                </div>

                <div v-else>
                    <div id="newsfeed-items-grid">
                    <div class="ui-block" v-for="(activity, index) in listActivity">
                        <article class="hentry post has-post-thumbnail thumb-full-width">
                            <div class="post__author author vcard inline-items">
                                <img :src="currentPageUser.image_thumbnail" alt="author" class="image-auth">
                                <div class="author-date">
                                    <router-link class="h6 post__author-name fn"
                                        :to="{ name: 'user.timeline', params: { slug: currentPageUser.slug }}">
                                        {{ currentPageUser.name }}
                                    </router-link>
                                    {{ detemineAction(activity) }}
                                    <span class="span-event">{{ nameActivity(activity.activitiable_type) }}</span>
                                    <router-link class="link-event" :to="url(activity.activitiable_type, activity.activitiable)">
                                        "<span class="title-event">{{ title(activity) }}</span>"
                                    </router-link>
                                    <span v-if="activity.activitiable_type != 'App\\Models\\Campaign'">
                                        - {{ $t('homepage.in_campaign') }}</span>
                                    <router-link class="link-event"
                                        v-if="activity.activitiable_type == 'App\\Models\\Event'"
                                        :to="url('App\\Models\\Campaign', activity.activitiable.campaign)">
                                        "<span class="title-event">{{ belongTo(activity) }}</span>"
                                    </router-link>
                                    <router-link class="link-event"
                                        v-if="activity.activitiable_type == 'App\\Models\\Action'"
                                        :to="url('App\\Models\\Campaign', activity.activitiable.event.campaign)">
                                        "<span class="title-event">{{ belongTo(activity) }}</span>"
                                    </router-link>
                                    <div class="post__date">
                                        <time class="published">
                                            {{ timeAgo(activity.activitiable.created_at) }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                            <list-image v-if="activity.activitiable.media.length" :listImage="activity.activitiable.media"></list-image>
                            <a href="javascript:void(0)"
                                @click="detailAction(activity.activitiable_id)"
                                v-if="activity.activitiable_type == 'App\\Models\\Action'"
                                :to="url(activity.activitiable_type, activity.activitiable)"
                                class="h2 post-title">
                                {{ activity.activitiable.caption }}
                            </a>
                            <router-link v-else
                                :to="url(activity.activitiable_type, activity.activitiable)"
                                class="h2 post-title">
                                {{ activity.activitiable.title }}
                            </router-link>

                            <p>
                                <show-text
                                    :text="activity.activitiable.description"
                                    :show_char=850
                                    :number_char_show=700
                                    :show="$t('events.show_more')"
                                    :hide="$t('events.show_less')">
                                </show-text>
                            </p>

                            <master-like
                                :likes="activity.activitiable.likes"
                                :checkLiked="checkLikes(activity.activitiable_type, checkLiked)"
                                :flag="nameActivity(activity.activitiable_type)"
                                :type="'like'"
                                :modelId="activity.activitiable.id"
                                :numberOfComments="activity.activitiable.number_of_comments"
                                :numberOfLikes="activity.activitiable.number_of_likes"
                                :deleteDate="activity.activitiable.deleted_at"
                                :showMore="true"
                                :roomLike="`user${currentPageUser.id}`">
                            </master-like>

                            <div class="control-block-button post-control-button">
                                <master-like
                                    :likes="activity.activitiable.likes"
                                    :checkLiked="checkLikes(activity.activitiable_type, checkLiked)"
                                    :flag="nameActivity(activity.activitiable_type)"
                                    :type="'like-infor'"
                                    :modelId="activity.activitiable.id"
                                    :numberOfComments="activity.activitiable.number_of_comments"
                                    :numberOfLikes="activity.activitiable.number_of_likes"
                                    :deleteDate="activity.activitiable.deleted_at"
                                    :roomLike="`user${currentPageUser.id}`">
                                </master-like>
                                <plugin-sidebar>
                                    <template scope="props" slot="sharing-social">
                                        <share-social-network
                                            :url="url(activity.activitiable_type, activity.activitiable)"
                                            :title="activity.activitiable.title"
                                            :description="activity.activitiable.description"
                                            :isSocialSharing="props.isPopupShare">
                                        </share-social-network>
                                    </template>
                                </plugin-sidebar>
                            </div>
                        </article>

                        <comment
                            :comments="activity.activitiable.comments"
                            :numberOfComments="activity.activitiable.number_of_comments"
                            :model-id ="activity.activitiable.id"
                            :flag="nameActivity(activity.activitiable_type)"
                            :classListComment="''"
                            :classFormComment="''"
                            :deleteDate="activity.activitiable.deleted_at"
                            :canComment="true"
                            :roomLike="`user${currentPageUser.id}`">
                        </comment>
                    </div>
                </div>
                <a href="javascript:void(0)" class="btn btn-control btn-more">
                    <i class="fa fa-spinner fa-pulse fa-spin fa-5x" v-if="loading"></i>
                </a>

                </div>
            </div>
            <!-- end Main Content -->

            <!-- Left Sidebar -->
            <Left-sidebar></Left-sidebar>
            <!-- end Left Sidebar -->

            <!-- Right Sidebar -->
            <Right-sidebar></Right-sidebar>
            <!-- end Right Sidebar -->

        </div>
        <action-detail
            :showAction.sync="showAction"
            :dataAction.sync="dataAction.list_action"
            :checkLikeActions.sync="dataAction.checkLikeAction"
            :canComment.sync="checkPermission">
        </action-detail>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import { get, post, del } from '../../helpers/api'
    import noty from '../../helpers/noty'
    import LeftSidebar from './timeline/Left-sidebar.vue'
    import RightSidebar from './timeline/Right-sidebar.vue'
    import MasterLike from '../like/MasterLike.vue'
    import Comment from '../comment/Comment.vue'
    import ActionDetail from '../event/ActionDetail.vue'
    import sideWaypoint from '../../helpers/mixin/sideWaypoint'
    import ShowText from '../libs/ShowText.vue'
    import ShareSocialNetwork from '../libs/ShareSocialNetwork.vue'
    import PluginSidebar from '../libs/PluginSidebar.vue'
    import ListImage from '../home/ListImage.vue'

    export default {
        data: () => ({
            showAction: false,
            dataAction: [],
            checkPermission: true
        }),
        computed: {
            ...mapState('user', [
                'listActivity',
                'loading',
                'checkLiked',
                'inforListActivity',
            ]),
            ...mapState('user', {
                currentPageUser: state => state.currentPageUser,
            }),
            ...mapState('auth', {
                user: state => state.user
            })
        },
        mixins: [sideWaypoint],
        mounted() {
            $(window).scroll(() => {
                if ($(document).height() - $(window).height() < $(window).scrollTop() + 1) {
                    this.loadMore({
                        id: this.pageId,
                        infoPaginate: this.inforListActivity
                    })
                }
            })
        },
        beforeDestroy() {
            $(window).off()
        },
        methods: {
            ...mapActions('user', ['loadMore']),
            ...mapActions('action', [
                'getDetailAction',
            ]),

            timeAgo(time) {
                return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
            },
            nameActivity(type) {
                switch(type) {
                    case 'App\\Models\\Campaign':
                        return this.$i18n.t('form.campaign')
                    case 'App\\Models\\Event':
                        return this.$i18n.t('form.event')
                    case 'App\\Models\\Action':
                        return this.$i18n.t('form.action')
                    default:
                        return ''
                }

            },
            detemineAction(activity) {
                switch(activity.name) {
                    case 'create':
                        if (activity.activitiable.deleted_at) {
                            return this.$i18n.t('form.closed')
                        }

                        return this.$i18n.t('form.created')
                    case 'update':
                        return this.$i18n.t('form.updated')
                    case 'delete':
                        return this.$i18n.t('form.deleted')
                    case 'join':
                        return this.$i18n.t('form.joined')
                    default:
                        return ''
                }
            },
            url(type, activity) {
                switch(type) {
                    case 'App\\Models\\Campaign':
                        return { name: 'campaign.timeline', params: { slug: activity.slug } }
                    case 'App\\Models\\Event':
                        return { name: 'event.index', params: { slug: activity.campaign_id, slugEvent: activity.slug } }
                    default:
                        return {}
                }
            },
            title(activity) {
                if (activity.activitiable_type == 'App\\Models\\Action') {
                    return activity.activitiable.caption
                }

                return activity.activitiable.title
            },
            belongTo(activity) {
                if (activity.activitiable_type == 'App\\Models\\Action') {
                    return activity.activitiable.event.campaign.title
                }

                if (activity.activitiable.campaign) {
                    return activity.activitiable.campaign.title
                }
            },
            checkLikes(type, checkLiked) {
                switch(type) {
                    case 'App\\Models\\Campaign':
                        return checkLiked.campaign
                    case 'App\\Models\\Event':
                        return checkLiked.event
                    case 'App\\Models\\Action':
                        return checkLiked.action
                    default:
                        return ''
                }
            },
            detailAction(actionId) {
                this.showAction = true
                this.getDetailAction(actionId)
                .then(data => {
                    this.dataAction = data.actions
                    this.checkPermission = data.checkPermission
                })
                .catch(err => {
                    this.showAction = false
                    const message = this.$i18n.t('messages.message-fail')
                    noty({ text: message, force: true, container: false })
                })
            },
        },
        sockets: {
            newLike: function (data) {
                if (this.user.id != data.user.id) {
                    this.appendLike(data)
                }
            }
        },
        components: {
            LeftSidebar,
            RightSidebar,
            MasterLike,
            Comment,
            ActionDetail,
            ShowText,
            ShareSocialNetwork,
            PluginSidebar,
            ListImage
        },
    }
</script>

<style lang="scss" scoped>
    .post {
        padding-bottom: 0px;
        border-bottom: 0;

        .h6 .post__author-name .fn {
            font-size: 16px !important;
        }

        .title-action {
            font-size: 17px !important;
        }

        .name-action {
            font-size: 17px !important;
            font-weight: bold !important;
        }

        .img-auth, .article-more {
            width: 3%;
        }

        .author-date {
            width: 80%;
        }

        .post__author {
            margin-bottom: 10px;
        }

        .post__date {
            margin-top: 5px;
        }

        .author-date {
            font-size: 14px;
            .link-event {
                color: rgb(97, 99, 115);
                text-transform: uppercase;
                font-weight: 400;
                .title-event {
                    color: #616373;
                    &:hover {
                        color: #fe5d39;
                    }
                }
            }
            .published {
                font-size: 13px;
            }
        }

        .span-event{
            color: #fe5d39;
        }
    }
</style>
