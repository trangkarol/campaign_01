<template>
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block features-video wrap-event">
                    <div class="slider-event video-player">
                        <slider animation="fade" height="100%" :interval="300000">
                            <slider-item v-for="(i, index) in event.media" :key="index">
                                <div :style="style">
                                    <img :src="i.image_slider" class="image-event">
                                </div>
                            </slider-item>
                        </slider>
                    </div>
                    <div class="features-video-content">
                        <article class="hentry post info-event">
                            <div class="post__author author vcard inline-items user-event">
                                <img :src="event.user.image_thumbnail" alt="author">
                                <div class="author-date">
                                    <router-link :to="{ name: 'user.timeline', params: { id: event.user.id } }">
                                        <a class="h6 post__author-name fn" href="javascript:void(0)">{{ event.user.name }}</a>
                                    </router-link>
                                    <div class="post__date">
                                        <time class="published" datetime="2017-03-24T18:18">
                                            {{ timeAgo(event.created_at) }}
                                        </time>
                                    </div>
                                </div>
                                <div class="more" v-if="isManager">
                                    <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                    <ul class="more-dropdown">
                                        <li>
                                            <router-link :to="{ name: 'event.update', params: { id: event.id } }">
                                                {{ $t('events.edit-event') }}
                                            </router-link>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)" @click="confirmCloseEvent()">{{ $t('events.close-event') }}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <p class="title-event">{{ event.title }}</p>
                            <div class="control-block-button post-control-button">
                                <master-like
                                    :likes="event.likes"
                                    :checkLiked="checkLiked"
                                    :flag="'event'"
                                    :type="'like-infor'"
                                    :modelId="event.id"
                                    :numberOfComments="event.number_of_comments"
                                    :numberOfLikes="event.number_of_likes">
                                </master-like>
                                <a href="javascript:void(0)" class="btn btn-control">
                                    <svg class="olymp-share-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use></svg>
                                </a>
                            </div>
                        </article>

                        <comment
                            :comments="event.comments"
                            :numberOfComments="event.number_of_comments"
                            :model-id ="event.id"
                            :flag="model"
                            :classListComment="'list-comment-event'"
                            :classFormComment="'input-comment-event'">
                        </comment>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block">
                    <span class="ui-block-title">
                        <show-text
                            :text="event.description"
                            :show_char=500
                            :number_char_show=300
                            :show="$t('events.show_more')"
                            :hide="$t('events.show_less')">
                        </show-text>
                    </span>
                </div>
            </div>
        </div>
        <message :show.sync="showComfirm">
            <h5 class="exclamation-header" slot="header">
                {{ $t('messages.comfirm_delete') }}
            </h5>
            <div class="body-modal confirm-delete" slot="main">
                <a href="javascript:void(0)"
                    class="btn btn-breez col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="closeEvent(event.id)">
                    {{ $t('form.button.agree') }}
                </a>
                <a href="javascript:void(0)"
                    class="btn btn-secondary col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="cancelCloseEvent">
                    {{ $t('form.button.no') }}
                </a>
            </div>
        </message>
    </div>
</template>

<script>
    import { Slider, SliderItem } from 'vue-easy-slider'
    import { mapState, mapActions } from 'vuex'
    import ShowText from '../libs/ShowText.vue'
    import Comment from '../comment/Comment.vue'
    import { get, del } from '../../helpers/api'
    import noty from '../../helpers/noty'
    import Message from '../libs/Modal.vue'
    import MasterLike from '../like/MasterLike.vue'

    export default {
        data :() => ({
            style: { width: '100%', height: '100%' },
            isLiked: null,
            numberLike: 0,
            model: 'event',
            showExpense: false,
            isManager: false,
            showComfirm: false
        }),

        computed : {
            ...mapState('event', [
                'event',
                'checkLiked'
            ]),
            ...mapState('auth', {
                user: state => state.user
            }),
        },

        mounted() {
            this.setScrollBar()
            this.eventPostComment()
        },

        methods: {
            timeAgo(time) {
                return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
            },

            ...mapActions('event', [
                'like_event'
            ]),

            closeEvent(id) {
                del(`event/delete/${id}`)
                    .then(res => {
                        noty({
                            text: this.$i18n.t('messages.delete_success'),
                            force: false,
                            container: false,
                            type: 'success'
                        })
                        this.$router.push({ name: 'campaign.timeline', params: { id: this.event.campaign_id }})
                    })
                    .catch(res => {
                        noty({
                            text: this.$i18n.t('messages.delete_fail'),
                            force: false,
                            container: false,
                            type: 'error'
                        })
                    })
            },

            confirmCloseEvent() {
                this.showComfirm = true
            },

            cancelCloseEvent() {
                this.showComfirm = false
            },

            setScrollBar() {
                $(".list-comment-event")[0].scrollTop = $(".list-comment-event")[0].scrollHeight;
            },

            eventPostComment() {
                $(".input-comment-event textarea").on('keyup', function(e) {
                    if(e.keyCode == 13) {
                        $(".list-comment-event")[0].scrollTop = $(".list-comment-event")[0].scrollHeight;
                    }
                })
            },

            show() {
                this.showExpense = true
            }
        },

        created() {
            get(`event/check-permission/${this.pageId}`)
                .then(res => {
                    this.isManager = res.data
                })
                .catch(res => {

                })
        },

        components: {
            Slider,
            SliderItem,
            ShowText,
            Comment,
            Message,
            MasterLike
        }
    }
</script>

<style lang="scss" scoped>
    .wrap-event {
        z-index: 20 !important;
        margin-top: 10px;
        margin-bottom: 0px;
        align-items: initial !important;
        .slider-event {
            height: 420px !important;
            button {
                z-index: 20 !important;
            }
            .image-event {
                width: 100% !important;
                height: 100% !important;
            }
            .slider-item {
                z-index: 19 !important;
            }
        }
        .info-event {
            padding: 5px 14px !important;
            .user-event {
                padding-bottom: 5px !important;
                margin-bottom: 0px;
                padding: 0;
                .author-date {
                    width: 60%;
                }
                .more {
                    margin-right: 5px;
                }
            }
            .title-event {
                padding: 0px !important;
                margin: 2px 0 !important;
            }
            .like-comment-event {
                padding:0px!important;
                .float-comment-shared {
                    float: left !important;
                    margin-right: 10px !important;
                }
            }
            .liked {
                background-color: #ff5e3a !important;
                &:hover {
                    background-color: #9a9fbf !important;
                }
            }
        }
        .list-comment-event {
            height: 231px;
            overflow-y: scroll;
            &::-webkit-scrollbar {
                display: none;
            }
            .has-children {
                span {
                    padding: 10px 0px;
                }
            }
        }
        .input-comment-event {
            padding: 3px 14px;
            textarea {
                min-height: 60px !important
            }
        }
    }
    .show-comment {
        max-height: 300px;
        overflow-y: scroll;
    }
    .color-like {
        fill: #ff5e3a !important;
        &:hover {
            fill: #9a9fbf !important;
        }
    }
</style>
