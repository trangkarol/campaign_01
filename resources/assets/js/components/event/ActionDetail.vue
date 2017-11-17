<template>
    <div v-if="showAction">
        <div class="modal fade show wrap-action" id="open-photo-popup-v1" style="display: block"
            v-if="dataAction" @click.self="close">
            <div class="modal-dialog ui-block window-popup open-photo-popup open-photo-popup-v1">
                <a href="javascript:void(0)" @click="close" class="close icon-close" data-dismiss="modal" aria-label="Close">
                    <svg class="olymp-close-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-close-icon"></use>
                    </svg>
                </a>
                <div class="open-photo-thumb">
                    <div class="swiper-container" data-slide="fade" ref="swiper">
                        <div class="swiper-wrapper" v-if="dataAction.media">
                            <div class="swiper-slide" v-for="(item, index) in dataAction.media">
                                <div class="photo-item">
                                    <img :src="item.image_default" alt="photo">
                                    <div class="overlay"></div>
                                    <a href="javascript:void(0)" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
                                    <a href="javascript:void(0)" class="tag-friends" data-toggle="tooltip" data-placement="top" title="" data-original-title="TAG YOUR FRIENDS">
                                        <svg class="olymp-happy-face-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use></svg>
                                    </a>

                                    <div class="content">
                                        <a href="javascript:void(0)" class="h6 title">{{ dataAction.caption }}</a>
                                        <time class="published" datetime="2017-03-24T18:18">
                                            <timeago :since="dataAction.created_at"/>
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--Prev Next Arrows-->

                        <div class="btn-next-without">
                            <svg class="olymp-popup-right-arrow">
                                <use xlink:href="/frontend/icons/icons.svg#olymp-popup-right-arrow"></use>
                            </svg>
                        </div>

                        <div class="btn-prev-without">
                            <svg class="olymp-popup-left-arrow">
                                <use xlink:href="/frontend/icons/icons.svg#olymp-popup-left-arrow"></use>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="open-photo-content">

                    <article class="hentry post" v-if="dataAction.user">

                        <div class="post__author author vcard inline-items">
                            <img :src="dataAction.user.image_thumbnail" alt="author">

                            <div class="author-date">
                                <router-link :to="{ name: 'user.timeline', params: { slug: dataAction.user_id } }">
                                    <a class="h6 post__author-name fn" href="javascript:void(0)">
                                        {{ dataAction.user.name }}
                                    </a>
                                </router-link>
                                <div class="post__date">
                                    <time class="published" datetime="2017-03-24T18:18">
                                        <timeago :since="dataAction.created_at"/>
                                    </time>
                                </div>
                            </div>
                        </div>

                        <p>
                            <span class="ui-block-title about-action">
                                <p v-if="!dataAction.expense_id" class='h3 caption-of-action'>{{ dataAction.caption }}</p>
                                <p v-else class='h3 caption-of-action'>{{ showTextExpense(dataAction.caption) }}</p>
                                <show-text
                                    :text="dataAction.description"
                                    :show_char=500
                                    :show="$t('events.show_more')"
                                    :hide="$t('events.show_less')">
                                </show-text>
                            </span>
                        </p>

                        <master-like
                            :likes="dataAction.likes"
                            :checkLiked="checkLikeActions"
                            :flag="'action'"
                            :type="'like'"
                            :modelId="dataAction.id"
                            :numberOfComments="dataAction.number_of_comments"
                            :numberOfLikes="dataAction.number_of_likes"
                            :showMore="true"
                            :deleteDate="dataAction.deleted_at"
                            :roomLike="roomLike">
                        </master-like>

                        <div class="control-block-button post-control-button">
                            <master-like
                                :likes="dataAction.likes"
                                :checkLiked="checkLikeActions"
                                :flag="'action'"
                                :type="'like-infor'"
                                :modelId="dataAction.id"
                                :numberOfComments="dataAction.number_of_comments"
                                :numberOfLikes="dataAction.number_of_likes"
                                :deleteDate="dataAction.deleted_at"
                                :roomLike="roomLike">
                            </master-like>

                            <a href="javascript:void(0)" class="btn btn-control">
                                <svg class="olymp-share-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use></svg>
                            </a>

                        </div>

                    </article>
                </div>
                <comment
                    v-if="dataAction.comments"
                    :comments="dataAction.comments"
                    :numberOfComments="dataAction.number_of_comments"
                    :model-id ="dataAction.id"
                    :flag="'action'"
                    :classListComment="''"
                    :classFormComment="''"
                    :deleteDate="dataAction.deleted_at"
                    :canComment="canComment"
                    :roomLike="roomLike">
                </comment>

            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    import { mapState, mapActions } from 'vuex'
    import ShowText from '../libs/ShowText.vue'
    import Comment from '../comment/Comment.vue'
    import MasterLike from '../like/MasterLike.vue'

    export default {
        props: {
            showAction: {},
            dataAction: {},
            checkLikeActions: {},
            canComment: true,
            roomLike: ''
        },

        data() {
            return {
                model: 'action'
            }
        },
        computed: {
            ...mapState('auth', {
                user: state => state.user
            }),
            ...mapState('event', ['event']),
        },

        updated() {
            this.swiper()
            this.setScrollBar()
            this.eventPostComment()
        },

        methods: {
            ...mapActions('like', [
                'appendLike',
            ]),

            close() {
                this.$emit('update:showAction', false)
                this.$emit('update:dataAction', {})
                this.$emit('update:checkLikeActions', {})
            },

            swiper() {
                var initIterator = 0;
                var $breakPoints = false;
                var swipers = {}

                $('.swiper-container').each(function () {
                    var $t = $(this);
                    var index = 'swiper-unique-id-' + initIterator;
                    $t.addClass('swiper-' + index + ' initialized').attr('id', index);
                    $t.find('.swiper-pagination').addClass('pagination-' + index);

                    var $effect = ($t.data('effect')) ? $t.data('effect') : 'slide',
                        $crossfade = ($t.data('crossfade')) ? $t.data('crossfade') : true,
                        $loop = ($t.data('loop') == false) ? $t.data('loop') : true,
                        $showItems = ($t.data('show-items')) ? $t.data('show-items') : 1,
                        $scrollItems = ($t.data('scroll-items')) ? $t.data('scroll-items') : 1,
                        $scrollDirection = ($t.data('direction')) ? $t.data('direction') : 'horizontal',
                        $mouseScroll = ($t.data('mouse-scroll')) ? $t.data('mouse-scroll') : false,
                        $autoplay = ($t.data('autoplay')) ? parseInt($t.data('autoplay'), 10) : 0,
                        $autoheight = ($t.hasClass('auto-height')) ? true: false,
                        $slidesSpace = ($showItems > 1) ? 20 : 0;

                    if ($showItems > 1) {
                        $breakPoints = {
                            480: {
                                slidesPerView: 1,
                                slidesPerGroup: 1
                            },
                            768: {
                                slidesPerView: 2,
                                slidesPerGroup: 2
                            }
                        }
                    }

                    swipers['swiper-' + index] = new Swiper('.swiper-' + index, {
                        pagination: '.pagination-' + index,
                        paginationClickable: true,
                        direction: $scrollDirection,
                        mousewheelControl: $mouseScroll,
                        mousewheelReleaseOnEdges: $mouseScroll,
                        slidesPerView: $showItems,
                        slidesPerGroup: $scrollItems,
                        spaceBetween: $slidesSpace,
                        keyboardControl: true,
                        setWrapperSize: true,
                        preloadImages: true,
                        updateOnImagesReady: true,
                        autoplay: $autoplay,
                        autoHeight: true,
                        loop: $loop,
                        breakpoints: $breakPoints,
                        effect: $effect,
                        fade: {
                            crossFade: $crossfade
                        },
                        parallax: true,
                        onSlideChangeStart: function (swiper) {
                            var sliderThumbs = $t.siblings('.slider-slides');
                            if (sliderThumbs.length) {
                                sliderThumbs.find('.slide-active').removeClass('slide-active');
                                var realIndex = swiper.slides.eq(swiper.activeIndex).attr('data-swiper-slide-index');
                                sliderThumbs.find('.slides-item').eq(realIndex).addClass('slide-active');
                            }
                        }
                    });
                    initIterator++;
                });

                $('.btn-prev-without').on('click', function () {
                    var sliderID = $(this).closest('.swiper-container').attr('id');
                    swipers['swiper-' + sliderID].slidePrev();
                });

                $('.btn-next-without').on('click', function () {
                    var sliderID = $(this).closest('.swiper-container').attr('id');
                    swipers['swiper-' + sliderID].slideNext();
                });
            },

            setScrollBar() {
                if ($(".list-comment-action")[0]) {
                    $(".list-comment-action")[0].scrollTop = $(".list-comment-action")[0].scrollHeight;
                }
            },

            eventPostComment() {
                $(".input-comment-action textarea").on('keyup', function(e) {
                    if(e.keyCode == 13) {
                        $(".list-comment-action")[0].scrollTop = $(".list-comment-action")[0].scrollHeight;
                    }
                })
            },

            showTextExpense(data) {
                var caption = JSON.parse(data);

                return `${caption.cost} ${caption.nameQuality}
                    ${caption.typeName} ${this.$i18n.t('actions.is_used')}
                    ${moment(caption.expenseTime, 'YYYY-MM-DD').format('DD/MM/YYYY')}`
            }
        },

        components: {
            ShowText,
            Comment,
            MasterLike
        },
        sockets: {
            newLike: function (data) {
                if (this.user.id != data.user.id) {
                    this.appendLike(data)
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    .wrap-action {
        background: rgba(0, 0, 0, 0.53);
        overflow-y: scroll;
        &::-webkit-scrollbar {
            display: none;
        }
        .open-photo-thumb {
            padding: 0px;
            .swiper-container {
                padding-bottom: 0px;
            }
        }
        .modal-dialog {
            margin-bottom: 100px;
        }
        .list-comment-action {
            max-height: 400px;
            overflow-y: scroll;
            &::-webkit-scrollbar {
                display: none;
            }
        }

        .about-action {
            .caption-of-action {
                display: block;
                font-size: 25px;
                margin-bottom: 17px;
            }
            span {
                display: block;
            }
        }

        .ui-block-title {
            padding: 0px 25px;
        }

        .btn-next-without {
            right: 0px;
            text-align: right;
        }

        .btn-prev-without {
            left: 0px;
            text-align: left;
        }

        .btn-next-without, .btn-prev-without {
            width: 30%;
            height: 100%;
            padding-top: 56%;
        }
    }
</style>
