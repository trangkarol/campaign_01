<template>
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-xl-6 push-xl-3 col-lg-12 push-lg-0 col-md-12 col-sm-12 col-xs-12">
                <div id="newsfeed-items-grid">
                    <div class="ui-block" v-for="(activity, index) in listActivity.data">
                        <article class="hentry post has-post-thumbnail thumb-full-width">
                            <div class="post__author author vcard inline-items">
                                <img :src="currentPageUser.url_file" alt="author" class="image-auth">
                                <div class="author-date">
                                    <a class="h6 post__author-name fn" href="javascript:void(0)">{{ currentPageUser.name }}</a>
                                    <span>{{ detemineAction(activity.name) }}</span>
                                    <router-link :to="url(activity.activitiable_type, activity.activitiable.slug)">
                                        {{ nameActivity(activity.activitiable_type) }}
                                    </router-link>
                                    <div class="post__date">
                                        <time class="published">
                                            {{ activity.created_at }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                            <div class="post-thumb" v-if="activity.activitiable.media != null">
                                <a href="javascript:void(0)" v-if="activity.activitiable_type == 'App\\Models\\Action'" @click="detailAction(activity.activitiable_id)">
                                    <img :src="activity.activitiable.media[0].image_medium" alt="photo">
                                </a>
                                <img :src="activity.activitiable.media[0].image_medium" alt="photo" v-else>
                            </div>
                            <router-link :to="url(activity.activitiable_type, activity.activitiable.slug)"
                                class="h2 post-title">
                                {{ activity.activitiable.title }}
                            </router-link>

                            <p v-html="activity.activitiable.description"></p>

                            <a href="javascript:void(0)" style="display: none;"
                                data-toggle="modal"
                                data-target="#blog-post-popup"
                                class="btn btn-md-2 btn-border-think c-grey btn-transparent custom-color">
                                {{ $t('post.read_more') }}
                            </a>

                            <master-like
                                :likes="activity.likes"
                                :checkLiked="checkLiked"
                                :flag="'activity'"
                                :type="'like'"
                                :modelId="activity.id"
                                :numberOfComments="activity.number_of_comments"
                                :numberOfLikes="activity.number_of_likes"
                                :showMore="true">
                            </master-like>

                            <div class="control-block-button post-control-button">
                                <master-like
                                    :likes="activity.likes"
                                    :checkLiked="checkLiked"
                                    :flag="'activity'"
                                    :type="'like-infor'"
                                    :modelId="activity.id"
                                    :numberOfComments="activity.number_of_comments"
                                    :numberOfLikes="activity.number_of_likes">
                                </master-like>
                                <a href="javascript:void(0)" class="btn btn-control">
                                    <svg class="olymp-comments-post-icon">
                                        <use xlink:href="/frontend/icons/icons.svg#olymp-comments-post-icon"></use>
                                    </svg>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-control">
                                    <svg class="olymp-share-icon">
                                        <use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use>
                                    </svg>
                                </a>
                            </div>
                        </article>
                        <comment
                            :comments="activity.comments"
                            :numberOfComments="activity.number_of_comments"
                            :model-id ="activity.id"
                            :flag="'activity'"
                            :classListComment="''"
                            :classFormComment="''">
                        </comment>
                    </div>
                </div>
                <a href="javascript:void(0)" class="btn btn-control btn-more" data-load-link="items-to-load.html" data-container="newsfeed-items-grid">
                    <i class="fa fa-spinner fa-pulse fa-spin fa-5x" v-if="loading"></i>
                </a>
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

    export default {
        data: () => ({
            showAction: false,
            dataAction: {},
            checkLikeAction: {},
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
        mounted() {
            $(window).scroll(() => {
                if ($(document).height() - $(window).height() < $(window).scrollTop() + 1) {
                    this.loadMore({
                        id: this.$route.params.id,
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
                if (type == 'App\\Models\\Campaign') {
                    return this.$i18n.t('form.campaign')
                }

                if (type == 'App\\Models\\Event') {
                    return this.$i18n.t('form.event')
                }

                if (type == 'App\\Models\\Action') {
                    return this.$i18n.t('form.action')
                }

            },
            detemineAction(name) {
                if (name == 'create') {
                    return this.$i18n.t('form.created')
                }

                if (name == 'update') {
                    return this.$i18n.t('form.updated')
                }

                if (name == 'delete') {
                    return this.$i18n.t('form.deleted')
                }

                if (name == 'join') {
                    return this.$i18n.t('form.joined')
                }
            },
            url(type, slug) {
                if (type == 'App\\Models\\Campaign') {
                    return { name: 'campaign.timeline',
                        params: { slug: slug }}
                }

                if (type == 'App\\Models\\Event') {
                    return { name: 'event.index',
                        params: { slug: slug }}
                }

                if (type == 'App\\Models\\Action') {
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
                        //
                    })
            }
        },
        components: {
            LeftSidebar,
            RightSidebar,
            MasterLike,
            Comment,
            ActionDetail
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

    .post-additional-info {
        border-bottom: 1px solid #e6ecf5;
        padding-bottom: 15px;
    }

    .post__author {
        margin-bottom: 10px;
    }

    .post__date {
        margin-top: 5px;
    }
</style>
