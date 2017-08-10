<template>
    <div class="container list-action">
        <div class="load-search" v-if="load_search"></div>
        <div class="empty center-block" v-if="isEmpty">
            <h2>
                {{ $t('events.not_found_action') }}
            </h2>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block" v-for="(action, index) in actions.list_action.data" v-if="!(index % 2)">
                    <article class="hentry post has-post-thumbnail thumb-full-width">
                        <div class="post__author author vcard inline-items">
                            <img :src="action.user.image_thumbnail" alt="author">
                            <div class="author-date">
                                <router-link :to="{ name: 'user.timeline', params: { id: action.user.id } }">
                                    <a class="h6 post__author-name fn" href="javascript:void(0)">{{ action.user.name }}</a>
                                </router-link>
                                <div class="post__date">
                                    <time class="published" datetime="2017-03-24T18:18">
                                        {{ timeAgo(action.created_at) }}
                                    </time>
                                </div>
                            </div>
                            <div class="more">
                            <svg class="olymp-three-dots-icon">
                                <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                            </svg>
                                <ul class="more-dropdown">
                                    <li v-if="user.id === action.user_id">
                                        <a href="javascript:void(0)" @click="updateAction(action)">{{ $t('actions.edit_action') }}</a>
                                    </li>
                                    <li v-if="user.id === action.user_id">
                                        <a href="javascript:void(0)" @click="comfirmDelete(action.id)">{{ $t('actions.delete_action') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="post-thumb one-image" v-if="action.media.length === 1" @click="detailAction(action)">
                            <img v-for="imgs in action.media" :src="imgs.image_medium">
                        </div>
                        <div class="post-thumb more-image" v-if="action.media.length > 1" @click="detailAction(action)">
                            <div class="wrap-img" v-for="(imgs, index) in action.media" v-if="index < numberImgShow">
                                <img :src="imgs.image_small">
                                <div v-if="index == numberImgShow - 1">
                                    <div v-if="action.media.length - numberImgShow"> + {{ action.media.length - numberImgShow }}</div>
                                </div>
                            </div>
                        </div>
                        <a
                            href="javascript:void(0)"
                            data-toggle="modal"
                            data-target="#blog-post-popup"
                            class="h2 post-title"
                            @click="detailAction(action)">
                                {{ action.caption }}
                        </a>
                        <show-text
                            :text="action.description"
                            :show_char=500
                            :show="$t('events.show_more')"
                            :hide="$t('events.show_less')">
                        </show-text>

                        <div class="control-block-button post-control-button">
                            <master-like
                                :likes="action.likes"
                                :checkLiked="actions.checkLikeAction"
                                :flag="'action'"
                                :type="'like-infor'"
                                :modelId="action.id"
                                :numberOfComments="action.number_of_comments"
                                :numberOfLikes="action.number_of_likes"
                                >
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
                </div>
            </div>
            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block" v-for="(action, index) in actions.list_action.data" v-if="index % 2">
                    <article class="hentry post has-post-thumbnail thumb-full-width">
                        <div class="post__author author vcard inline-items">
                            <img :src="action.user.image_thumbnail" alt="author">
                            <div class="author-date">
                                <router-link :to="{ name: 'user.timeline', params: { id: action.user.id } }">
                                    <a class="h6 post__author-name fn" href="javascript:void(0)">{{ action.user.name }}</a>
                                </router-link>
                                <div class="post__date">
                                    <time class="published" datetime="2017-03-24T18:18">
                                        {{ timeAgo(action.created_at) }}
                                    </time>
                                </div>
                            </div>
                            <div class="more" v-if="user.id === action.user_id">
                                <svg class="olymp-three-dots-icon">
                                    <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                </svg>
                                <ul class="more-dropdown">
                                    <li>
                                        <a href="javascript:void(0)" @click="updateAction(action)">{{ $t('actions.edit_action') }}</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" @click="comfirmDelete(action.id)">{{ $t('actions.delete_action') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="post-thumb one-image" v-if="action.media.length === 1" @click="detailAction(action)">
                            <img v-for="imgs in action.media" :src="imgs.image_medium">
                        </div>
                        <div class="post-thumb more-image" v-if="action.media.length > 1" @click="detailAction(action)">
                            <div class="wrap-img" v-for="(imgs, index) in action.media" v-if="index < numberImgShow">
                                <img :src="imgs.image_small">
                                <div v-if="index == numberImgShow - 1">
                                    <div v-if="action.media.length - numberImgShow"> + {{ action.media.length - numberImgShow }}</div>
                                </div>
                            </div>
                        </div>
                        <a
                            href="javascript:void(0)"
                            data-toggle="modal"
                            data-target="#blog-post-popup"
                            class="h2 post-title"
                            @click="detailAction(action)">
                                {{ action.caption }}
                        </a>
                        <show-text
                            :text="action.description"
                            :show_char=500
                            :show="$t('events.show_more')"
                            :hide="$t('events.show_less')">
                        </show-text>
                        <div class="control-block-button post-control-button">
                            <master-like
                                :likes="action.likes"
                                :checkLiked="actions.checkLikeAction"
                                :flag="'action'"
                                :type="'like-infor'"
                                :modelId="action.id"
                                :numberOfComments="action.number_of_comments"
                                :numberOfLikes="action.number_of_likes"
                                >
                            </master-like>

                            <a href="javascript:void(0)" class="btn btn-control">
                                <svg class="olymp-comments-post-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-comments-post-icon"></use></svg>
                            </a>
                            <a href="javascript:void(0)" class="btn btn-control">
                                <svg class="olymp-share-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use></svg>
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
        <message :show.sync="isShowDelete">
            <h5 class="exclamation-header" slot="header">
                {{ $t('messages.comfirm_delete') }}
            </h5>
            <div class="body-modal confirm-delete" slot="main">
                <a href="javascript:void(0)"
                    class="btn btn-breez col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="deleteAction">
                    {{ $t('form.button.agree') }}
                </a>
                <a href="javascript:void(0)" class="btn btn-secondary col-lg-3 col-md-6 col-sm-12 col-xs-12">
                    {{ $t('form.button.no') }}
                </a>
            </div>
        </message>
        <action-detail :showAction.sync="showAction" :dataAction = "dataAction" :checkLikeActions ="actions.checkLikeAction" ></action-detail>
        <update-action :showUpdate.sync="showUpdate" v-if="showUpdate" :dataAction = "dataAction"></update-action>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import ShowText from '../libs/ShowText.vue'
    import ActionDetail from './ActionDetail.vue'
    import UpdateAction from './UpdateAction.vue'
    import MasterLike from '../like/MasterLike.vue'
    import Message from '../libs/Modal.vue'
    import { del } from '../../helpers/api'
    import noty from '../../helpers/noty'

    export default {
        data: () => ({
            numberImgShow: 4,
            showAction: false,
            showUpdate: false,
            dataAction: {},
            checkLikeActions: [],
            isShowDelete: false,
            actionId: null
        }),

        computed: {
            ...mapState('event', [
                'actions',
                'flag_search',
                'key_search',
                'load_search',
                'load_paginate'
            ]),

            ...mapState('auth', {
                user: state => state.user
            }),

            isEmpty() {
                return !this.actions.list_action.data.length
            }
        },

        mounted() {
            $(window).scroll(() => {
                if ($(document).height() - $(window).height() < $(window).scrollTop() + 1) {
                    this.load_action({
                        event_id: this.pageId,
                        actions: his.actions.list_action,
                        flag_search: this.flag_search,
                        key: this.key_search
                    })
                }
            })
        },

        methods: {
            timeAgo(time) {
                return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
            },

            ...mapActions('event', [
                'load_action',
                'removeAction'
            ]),

            detailAction(data) {
                this.dataAction = data
                this.showAction = true
            },

            updateAction(data) {
                this.dataAction = data
                this.showUpdate = true
            },

            comfirmDelete(id) {
                this.actionId = id
                this.isShowDelete = true
            },

            cancelDelete() {
                this.actionId = null
                this.isShowDelete = false
            },

            deleteAction() {

                del(`action/delete/${this.actionId}`)
                    .then(res => {
                        this.$Progress.finish()
                        this.removeAction(this.actionId)
                        this.isShowDelete = false
                        noty({
                            text: this.$i18n.t('messages.delete_success'),
                            force: false,
                            container: false,
                            type: 'success'
                        })
                    })
                    .catch(err => {
                        this.$Progress.fail()
                        noty({
                            text: this.$i18n.t('messages.delete_fail'),
                            type: 'error',
                            force: false,
                            container: false
                        })
                    })
            }
        },

        components : {
            ShowText,
            ActionDetail,
            UpdateAction,
            MasterLike,
            Message
        }
    }
</script>

<style lang="scss">
    .list-action {
        .load-search {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 999;
            background: url(/images/loading.gif) 50% 10% no-repeat rgba(235, 242, 242, 0.66);
            opacity: .6;
            background-size: 120px 120px;
        }
        .loading-more {
            background: url(/images/loading.gif) 50% 10% no-repeat rgba(235, 242, 242, 0.66);
            opacity: 1;
            background-size: 40px 40px;
            width: 100%;
            height: 60px;
        }
        .empty {
            z-index: 20;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            h2 {
                color: #c2c5d9;
                text-align: center;
                margin-top: 50px ;
            }
        }
        .post {
            .one-image {
                img {
                    width: auto;
                }

                &:hover {
                    cursor:pointer;
                }
            }
            .more-image {
                .wrap-img {
                    width: 50%;
                    display: inline-block;
                    position: relative;
                    img {
                        width: 100%;
                        border: 1px solid white;
                        border-radius: 5px;
                    }
                    div {
                        position: absolute;
                        display: table;
                        width: 100%;
                        height: inherit;
                        bottom: 0;
                        right: 0;
                        div {
                            display: table-cell;
                            text-align: right;
                            padding-right: 5px;
                            vertical-align: middle;
                            background: rgba(173, 143, 143, 0);
                            font-size: 50px;
                            color: white;
                            opacity: 0.7;
                        }
                    }
                }

                &:hover {
                    cursor:pointer;
                }
            }
            p {
                margin: 10px 0;
                line-height: 20px;
                text-align: justify;
            }
        }
    }
</style>
