<template>
    <div class="list-action-event">
        <div class="load-search" v-if="load_search"></div>
        <div class="empty center-block" v-if="isEmpty">
            <h2>
                {{ $t('events.not_found_action') }}
            </h2>
        </div>
        <div class="row" v-if="event">
            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block" v-for="(action, index) in actions.list_action.data" v-if="!(index % 2)">
                    <article class="hentry post has-post-thumbnail thumb-full-width">
                        <div class="post__author author vcard inline-items">
                            <img :src="action.user.image_thumbnail" alt="author">
                            <div class="author-date">
                                <router-link :to="{ name: 'user.timeline', params: { slug: action.user.slug } }">
                                    <a class="h6 post__author-name fn" href="javascript:void(0)">{{ action.user.name }}</a>
                                </router-link>
                                <div class="post__date">
                                    <time class="published" datetime="2017-03-24T18:18">
                                        <timeago :since="action.created_at"/>
                                    </time>
                                </div>
                            </div>
                            <div class="more">
                                <svg class="olymp-three-dots-icon" v-if="user.id === action.user_id && !action.expense_id">
                                    <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                </svg>
                                <ul class="more-dropdown" v-if="user.id === action.user_id && !action.expense_id">
                                    <li>
                                        <a href="javascript:void(0)" @click="updateAction(action)">{{ $t('actions.edit_action') }}</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" @click="comfirmDelete(action.id)">{{ $t('actions.delete_action') }}</a>
                                    </li>
                                </ul>
                                <img v-if="!action.expense_id" class="img-action" src="/images/action.png">
                            </div>
                        </div>
                        <div class="post-thumb one-image" v-if="action.media.length === 1" @click="detailAction(action.id)">
                            <img v-for="imgs in action.media" :src="imgs.image_medium">
                        </div>
                        <div class="post-thumb more-image" v-if="action.media.length > 1" @click="detailAction(action.id)">
                            <div class="wrap-img" v-for="(imgs, index) in action.media" v-if="index < numberImgShow">
                                <img :src="imgs.image_small">
                                <div v-if="index == numberImgShow - 1">
                                    <div v-if="action.media.length - numberImgShow"> + {{ action.media.length - numberImgShow }}</div>
                                </div>
                            </div>
                        </div>
                        <a v-if="!action.expense_id"
                            href="javascript:void(0)"
                            data-toggle="modal"
                            data-target="#blog-post-popup"
                            class="h2 post-title"
                            @click="detailAction(action.id)">
                            {{ action.caption }}
                        </a>
                        <a v-else
                            href="javascript:void(0)"
                            data-toggle="modal"
                            data-target="#blog-post-popup"
                            class="title-donation h3 post-title"
                            @click="detailAction(action.id)">
                            <span class="color-primary">
                                <img class="img-donation" src="/images/donation.png">
                                {{ formatJson(action.caption, 1) }}
                            </span>
                            {{ $t('actions.is_used') + ' ' + formatJson(action.caption, 0) }}
                        </a>
                        <p class="reason" v-if="action.expense_id">{{ $t('actions.with_reason') }} </p>
                        <div :class="{ 'show-reason': action.expense_id }">
                            <show-text
                                :text="action.description"
                                :show_char=500
                                :show="$t('events.show_more')"
                                :hide="$t('events.show_less')">
                            </show-text>
                        </div>
                        <div class="control-block-button post-control-button">
                            <master-like
                                :likes="action.likes"
                                :checkLiked="actions.checkLikeAction"
                                :flag="'action'"
                                :type="'like-infor'"
                                :modelId="action.id"
                                :numberOfComments="action.number_of_comments"
                                :numberOfLikes="action.number_of_likes"
                                :deleteDate="action.deleted_at"
                                :roomLike="`campaign${event.campaign_id}`">
                            </master-like>
                        </div>
                        <master-like
                            :likes="action.likes"
                            :checkLiked="actions.checkLikeAction"
                            :flag="'action'"
                            :type="'like'"
                            :modelId="action.id"
                            :numberOfComments="action.number_of_comments"
                            :numberOfLikes="action.number_of_likes"
                            :showMore="true"
                            :deleteDate="action.deleted_at"
                            :roomLike="`campaign${event.campaign_id}`">
                        </master-like>
                    </article>
                </div>
            </div>
            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block" v-for="(action, index) in actions.list_action.data" v-if="index % 2">
                    <article class="hentry post has-post-thumbnail thumb-full-width">
                        <div class="post__author author vcard inline-items">
                            <img :src="action.user.image_thumbnail" alt="author">
                            <div class="author-date">
                                <router-link :to="{ name: 'user.timeline', params: { slug: action.user.slug } }">
                                    <a class="h6 post__author-name fn" href="javascript:void(0)">{{ action.user.name }}</a>
                                </router-link>
                                <div class="post__date">
                                    <time class="published" datetime="2017-03-24T18:18">
                                        <timeago :since="action.created_at"/>
                                    </time>
                                </div>
                            </div>
                            <div class="more">
                                <svg class="olymp-three-dots-icon" v-if="user.id === action.user_id && !action.expense_id">
                                    <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                </svg>
                                <ul class="more-dropdown" v-if="user.id === action.user_id && !action.expense_id">
                                    <li>
                                        <a href="javascript:void(0)" @click="updateAction(action)">{{ $t('actions.edit_action') }}</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" @click="comfirmDelete(action.id)">{{ $t('actions.delete_action') }}</a>
                                    </li>
                                </ul>
                                <img v-if="!action.expense_id" class="img-action" src="/images/action.png">
                            </div>
                        </div>
                        <div class="post-thumb one-image" v-if="action.media.length === 1" @click="detailAction(action.id)">
                            <img v-for="imgs in action.media" :src="imgs.image_medium">
                        </div>
                        <div class="post-thumb more-image" v-if="action.media.length > 1" @click="detailAction(action.id)">
                            <div class="wrap-img" v-for="(imgs, index) in action.media" v-if="index < numberImgShow">
                                <img :src="imgs.image_small">
                                <div v-if="index == numberImgShow - 1">
                                    <div v-if="action.media.length - numberImgShow"> + {{ action.media.length - numberImgShow }}</div>
                                </div>
                            </div>
                        </div>
                        <a v-if="!action.expense_id"
                            href="javascript:void(0)"
                            data-toggle="modal"
                            data-target="#blog-post-popup"
                            class="h2 post-title"
                            @click="detailAction(action.id)">
                            {{ action.caption }}
                        </a>
                        <a v-else
                            href="javascript:void(0)"
                            data-toggle="modal"
                            data-target="#blog-post-popup"
                            class="title-donation h3 post-title"
                            @click="detailAction(action.id)">
                            <span class="color-primary">
                                <img class="img-donation" src="/images/donation.png">
                                {{ formatJson(action.caption, 1) }}
                            </span>
                            {{ $t('actions.is_used') + ' ' + formatJson(action.caption, 0) }}
                        </a>
                        <p class="reason" v-if="action.expense_id">{{ $t('actions.with_reason') }} </p>
                        <div :class="{ 'show-reason': action.expense_id }">
                            <show-text
                                :text="action.description"
                                :show_char=500
                                :show="$t('events.show_more')"
                                :hide="$t('events.show_less')">
                            </show-text>
                        </div>
                        <div class="control-block-button post-control-button">
                            <master-like
                                :likes="action.likes"
                                :checkLiked="actions.checkLikeAction"
                                :flag="'action'"
                                :type="'like-infor'"
                                :modelId="action.id"
                                :numberOfComments="action.number_of_comments"
                                :numberOfLikes="action.number_of_likes"
                                :deleteDate="action.deleted_at"
                                :roomLike="`campaign${event.campaign_id}`">
                            </master-like>
                        </div>
                        <master-like
                            :likes="action.likes"
                            :checkLiked="actions.checkLikeAction"
                            :flag="'action'"
                            :type="'like'"
                            :modelId="action.id"
                            :numberOfComments="action.number_of_comments"
                            :numberOfLikes="action.number_of_likes"
                            :showMore="true"
                            :deleteDate="action.deleted_at"
                            :roomLike="`campaign${event.campaign_id}`">
                        </master-like>
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
                <a href="javascript:void(0)"
                    class="btn btn-secondary col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="cancelDelete">
                    {{ $t('form.button.no') }}
                </a>
            </div>
        </message>
        <action-detail
            :showAction.sync="showAction"
            :dataAction.sync="dataAction.list_action"
            :checkLikeActions.sync="dataAction.checkLikeAction"
            :canComment.sync="checkPermission"
            :roomLike="`campaign${event.campaign_id}`">
        </action-detail>
        <update-action
            :showUpdate.sync="showUpdate"
            v-if="showUpdate"
            :dataAction="dataAction">
        </update-action>
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
            dataAction: [],
            isShowDelete: false,
            actionId: null,
            pageType: 'event',
            checkPermission: true
        }),

        computed: {
            ...mapState('event', [
                'actions',
                'flag_search',
                'key_search',
                'load_search',
                'load_paginate',
                'event'
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
                        actions: this.actions.list_action,
                        flag_search: this.flag_search,
                        key: this.key_search
                    })
                }
            })
        },

        methods: {
            ...mapActions('event', [
                'load_action',
                'removeAction',
                'appendOneAction',
                'updateDataAction',
            ]),

            ...mapActions('action', [
                'getDetailAction',
            ]),

            ...mapActions('like', [
                'appendLike',
            ]),

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
                this.isShowDelete = false
                del(`action/delete/${this.actionId}`)
                    .then(res => {
                        this.$Progress.finish()
                        this.$socket.emit('remove_action', {
                            actionId: this.actionId,
                            room: `event${this.pageId}`
                        })
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
            },

            formatJson(data, type) {
                var caption = JSON.parse(data);

                return (type)
                    ? `${caption.cost} ${caption.nameQuality} ${caption.typeName}`
                    : `${moment(caption.expenseTime, 'YYYY-MM-DD').format('L')}`
            }
        },

        components : {
            ShowText,
            ActionDetail,
            UpdateAction,
            MasterLike,
            Message
        },

        sockets: {
            new_action_created: function (data) {
                this.appendOneAction(data)
            },

            update_data_action: function (data) {
                this.updateDataAction(data.action)
            },

            delete_action: function(data) {
                this.removeAction(data.actionId)
            },
            newLike: function (data) {
                if (this.user.id != data.user.id) {
                    this.appendLike(data)
                }
            }
        },

        beforeDestroy() {
            $(window).off()
        }
    }
</script>

<style lang="scss">
    .list-action-event {
        position: relative;
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
        .action-expense {
            position: absolute;
            top: 14px;
            left: 0px;
            width: 30px;
            height: 30px;
            background-color: #9a9fbf;
            border-radius: 50%;
            text-align: center;
            i {
                color: white;
                font-size: 30px;
            }
        }

        .img-donation {
            width: 40px;
            height: 40px;
            margin-right: 5px;
        }

        .title-donation {
            font-size: 27px;
        }

        .reason {
            margin: 10px 0 0 !important;
            display: inline-block;
            color: #f45e3a;
            margin-right: 3px !important;
        }

        .show-reason {
            display: inline-block;
        }

        .img-action {
            border-radius: 0px !important;
            margin-left: 10px !important;
            margin-right: -12px !important;
            width: 30px !important;
            height: 30px !important;
        }
    }

    .color-primary {
        color: #ff5e3a;
    }
</style>
