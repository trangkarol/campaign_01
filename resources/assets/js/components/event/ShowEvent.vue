<template>
    <div>
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="wrap-event ui-block features-video">
                    <div class="slider-event video-player">
                        <slider v-if="event.media.length" animation="fade" height="100%" :interval="300000">
                            <slider-item v-for="(i, index) in event.media" :key="index">
                                <div :style="style">
                                    <img :src="i.image_slider" class="image-event">
                                </div>
                            </slider-item>
                        </slider>
                        <img v-else :src="imageEventDefault" class="image-event">
                    </div>
                    <div class="features-video-content">
                        <article class="hentry post info-event">
                            <div class="post__author author vcard inline-items user-event">
                                <div class="author-date">
                                    <p class="h2 post-title">
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                        {{ event.title }}
                                    </p>
                                </div>
                                <div class="more" v-if="isManager && !event.deleted_at">
                                    <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                    <ul class="more-dropdown">
                                        <li>
                                            <router-link :to="{ name: 'event.update', params: { slug: event.slug }}">
                                                {{ $t('events.edit-event') }}
                                            </router-link>
                                        </li>
                                        <li>
                                            <a href="#" @click.prevent="showConfirm = true">{{ $t('events.close-event') }}</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="post__date">
                                    <router-link
                                        :to="{ name: 'user.timeline', params: { slug: event.user.slug } }">
                                        <i class="fa fa-user" aria-hidden="true"></i> {{ event.user.name }}
                                    </router-link>
                                    <i class="fa fa-clock-o"></i> {{ timeAgo(event.created_at) }}
                                </div>
                            </div>
                            <master-like
                                :likes="event.likes"
                                :checkLiked="checkLiked"
                                :flag="'event'"
                                :type="'like'"
                                :modelId="event.id"
                                :numberOfComments="event.number_of_comments"
                                :numberOfLikes="event.number_of_likes"
                                :showMore="true"
                                :deleteDate="event.deleted_at"
                                :roomLike="`campaign${event.campaign_id}`">
                            </master-like>
                            <div class="control-block-button post-control-button">
                                <master-like
                                    :likes="event.likes"
                                    :checkLiked="checkLiked"
                                    :flag="'event'"
                                    :type="'like-infor'"
                                    :modelId="event.id"
                                    :numberOfComments="event.number_of_comments"
                                    :numberOfLikes="event.number_of_likes"
                                    :deleteDate="event.deleted_at"
                                    :roomLike="`campaign${event.campaign_id}`">
                                </master-like>
                                <plugin-sidebar>
                                    <template scope="props" slot="sharing-social">
                                        <share-social-network
                                            :url="{
                                                name: 'event.index',
                                                params: {
                                                    slug: event.campaign_id,
                                                    slugEvent: event.slug
                                                }
                                            }"
                                            :title="event.title"
                                            :description="event.description"
                                            :isSocialSharing="props.isPopupShare">
                                        </share-social-network>
                                    </template>
                                </plugin-sidebar>
                            </div>
                        </article>
                        <div class="show-comments">
                            <comment
                                :comments="event.comments"
                                :numberOfComments="event.number_of_comments"
                                :model-id ="event.id"
                                :flag="pageType"
                                :classListComment="'list-comment-event'"
                                :classFormComment="'input-comment-event'"
                                :deleteDate="event.deleted_at"
                                :canComment="event.isMember"
                                :roomLike="`campaign${event.campaign_id}`">
                            </comment>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block show-description">
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
        <message
            :show.sync="showConfirm"
            :messages="$t('messages.confirm_close_event')"
            @handelMethod="closeEvent(event.id)">
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
    import Message from '../libs/MessageComfirm.vue'
    import MasterLike from '../like/MasterLike.vue'
    import ShareSocialNetwork from '../libs/ShareSocialNetwork.vue'
    import PluginSidebar from '../libs/PluginSidebar.vue'

    export default {
        data: () => ({
            style: { width: '100%', height: '100%' },
            isLiked: null,
            numberLike: 0,
            model: 'event',
            showExpense: false,
            showConfirm: false,
            pageType: 'event',
            imageEventDefault: window.Laravel.settings.imageEventDefault
        }),

        computed: {
            ...mapState('event', [
                'event',
                'checkLiked'
            ]),
            ...mapState('auth', {
                user: state => state.user
            }),
            isManager() {
                return this.event.manage
            }
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

            ...mapActions('like', [
                'appendLike'
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
                        this.$router.push({ name: 'campaign.events_closed', params: { slug: this.event.campaign_id }})
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

            setScrollBar() {
                $(".list-comment-event")[0].scrollTop = $(".list-comment-event")[0].scrollHeight;
            },

            eventPostComment() {
                $(".input-comment-event textarea").on('keyup', function(e) {
                    if(e.keyCode == 13) {
                        $(".list-comment-event")[0].scrollTop = $(".list-comment-event")[0].scrollHeight
                    }
                })
            },

            show() {
                this.showExpense = true
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
            Slider,
            SliderItem,
            ShowText,
            Comment,
            Message,
            MasterLike,
            ShareSocialNetwork,
            PluginSidebar
        }
    }
</script>

<style lang="scss" scoped>
    .show-comments {
        height: 225px;
        overflow-y: scroll;
        &::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
            background-color: #F5F5F5;
        }

        &::-webkit-scrollbar {
            width: 2px;
            background-color: #ff5e3a;
        }

        &::-webkit-scrollbar-thumb {
            background-color: #7a7fa0;
        }
    }
    .wrap-event {
        z-index: 20 !important;
        margin-top: 10px;
        margin-bottom: 0px;
        align-items: initial !important;
        .slider-event {
            button {
                z-index: 20 !important;
            }
            .image-event {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover;
            }
            .slider-item {
                z-index: 19 !important;
            }
        }
        .info-event {
            padding: 15px 15px 0px !important;
            border-bottom: 0px;
            .user-event {
                margin-bottom: 0px;
                padding: 0;
                .author-date {
                    width: 60%;
                }
                .more {
                    margin-right: 5px;
                }
                .post__date {
                    a {
                        margin-left: 10px;
                        font-weight: 400;
                    }
                }
            }
            .title-event {
                padding: 0px !important;
                margin: 2px 0 !important;
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
    .show-description {
        .ui-block-title {
            line-height: 1.5
        }
    }
    .post {
        .author-date {
            width: 85% !important;
            margin-left: 5px;
        }
    }
    .post__date {
        margin-top: 5px;
        a {
            margin-right: 5px;
            color: #888da8;
        }
    }
    .post-additional-info {
        > * {
            margin-right: 0;
        }
    }
</style>
