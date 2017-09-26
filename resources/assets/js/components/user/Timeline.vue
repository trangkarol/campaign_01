<template>
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-xl-6 push-xl-3 col-lg-12 push-lg-0 col-md-12 col-sm-12 col-xs-12" v-if="listActivity">
                <div class="page-description" v-if="!listActivity.total">
                    <div class="icon">
                        <svg class="olymp-star-icon left-menu-icon">
                            <use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use>
                        </svg>
                    </div>
                    <span>{{ $t('user.activity_empty') }}</span>
                </div>

                <div v-else>
                    <div id="newsfeed-items-grid">
                    <div class="ui-block" v-for="(activity, index) in listActivity.data">
                        <article class="hentry post has-post-thumbnail thumb-full-width">
                            <div class="post__author author vcard inline-items">
                                <img :src="currentPageUser.image_thumbnail" alt="author" class="image-auth">
                                <div class="author-date">
                                    <a class="h6 post__author-name fn" href="javascript:void(0)">{{ currentPageUser.name }}</a>
                                    <span>{{ detemineAction(activity.name) }}</span>
                                    <router-link :to="url(activity.activitiable_type, activity.activitiable)">
                                        {{ nameActivity(activity.activitiable_type) }}
                                    </router-link>
                                    <div class="post__date">
                                        <time class="published">
                                            {{ timeAgo(activity.created_at) }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                            <div class="post-thumb" v-if="activity.activitiable.media.length">
                                <a href="javascript:void(0)"
                                    v-if="activity.activitiable_type == 'App\\Models\\Action'"
                                    @click="detailAction(activity.activitiable_id)">
                                    <img :src="activity.activitiable.media[0].image_medium" alt="photo">
                                </a>
                                <img :src="activity.activitiable.media[0].image_medium" alt="photo" v-else>
                            </div>
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
                                :checkLiked="checkLiked"
                                :flag="nameActivity(activity.activitiable_type)"
                                :type="'like'"
                                :modelId="activity.activitiable.id"
                                :numberOfComments="activity.activitiable.number_of_comments"
                                :numberOfLikes="activity.activitiable.number_of_likes"
                                :showMore="true">
                            </master-like>

                            <div class="control-block-button post-control-button">
                                <master-like
                                    :likes="activity.activitiable.likes"
                                    :checkLiked="checkLiked"
                                    :flag="nameActivity(activity.activitiable_type)"
                                    :type="'like-infor'"
                                    :modelId="activity.activitiable.id"
                                    :numberOfComments="activity.activitiable.number_of_comments"
                                    :numberOfLikes="activity.activitiable.number_of_likes">
                                </master-like>
                                <a href="javascript:void(0)" class="btn btn-control">
                                    <svg class="olymp-comments-post-icon">
                                        <use xlink:href="/frontend/icons/icons.svg#olymp-comments-post-icon"></use>
                                    </svg>
                                </a>
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
                            :model-id="activity.activitiable.id"
                            :flag="nameActivity(activity.activitiable_type)"
                            :classListComment="''"
                            :classFormComment="''">
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
            :dataAction="dataAction"
            :checkLikeActions="checkLikeAction">
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

    export default {
        data: () => ({
            showAction: false,
            dataAction: {},
            checkLikeAction: {}
        }),
        computed: {
            ...mapState('user', [
                'listActivity',
                'loading',
                'checkLiked',
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
                        infoPaginate: this.listActivity
                    })
                }
            })
        },
        beforeDestroy() {
            $(window).off()
        },
        methods: {
            ...mapActions('user', ['loadMore']),

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
            detemineAction(name) {
                switch(name) {
                    case 'create':
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
            detailAction(actionId) {
                get(`action/${actionId}`)
                    .then(res => {
                        this.showAction = true
                        this.dataAction = res.data.actions.list_action
                        this.checkLikeAction = res.data.actions.checkLikeAction
                    })
                    .catch(err => {
                        noty({
                            text: this.$i18n.t('messages.error'),
                            type: 'error',
                            force: false,
                            container: false
                        })
                    })
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
            PluginSidebar
        },
    }
</script>

<style lang="scss" scoped>
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
</style>
