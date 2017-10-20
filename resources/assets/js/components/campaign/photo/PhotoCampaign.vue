<template lang="html">
    <div class="row">
       <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="album-page" role="tabpanel" v-if="listPhotos.list_action">
                    <div class="photo-album-wrapper" v-if="listPhotos.list_action.total > 0">
                        <div class="photo-album-item-wrap col-4-width" v-for="photo in listPhotos.list_action.data">
                            <div class="photo-album-item" data-mh="album-item">
                                <div class="photo-item" v-if="photo.media != null">
                                    <a href="javascript:void(0)">
                                        <img :src="photo.media[0].image_small" alt="photo">
                                    </a>
                                    <div class="overlay overlay-dark"></div>
                                    <a href="javascript:void(0)" class="more">
                                        <svg class="olymp-three-dots-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                        </svg>
                                    </a>
                                    <master-like
                                        :likes="photo.likes"
                                        :checkLiked="listPhotos.checkLikeAction"
                                        :flag="'action'"
                                        :type="'like'"
                                        :modelId="photo.id"
                                        :numberOfComments="photo.number_of_comments"
                                        :numberOfLikes="photo.number_of_likes"
                                        :class="'post-add-icon'"
                                        :showMore="false"
                                        :deleteDate="photo.deleted_at"
                                        :roomLike="`campaign${pageId}`">
                                    </master-like>
                                    <a href="javascript:void(0)"
                                        data-toggle="modal"
                                        data-target="#open-photo-popup-v2"
                                        class="full-block"
                                        @click="detailAction(photo.id)">
                                    </a>
                                </div>
                                <div class="content">
                                    <show-text
                                        :type="false"
                                        :text="photo.caption"
                                        :show_char=300
                                        :show="$t('events.show_more')"
                                        :hide="$t('events.show_less')"
                                        :number_char_show=200
                                        class="title p">
                                    </show-text>
                                    <span class="sub-title"><timeago :since="photo.created_at"/></span>
                                    <div class="swiper-container" data-slide="fade">
                                        <div class="swiper-wrapper">
                                            <div class="swiper-slide">
                                                <ul class="friends-harmonic" >
                                                    <li v-for="(like, index) in photo.likes" v-if="index <= 6">
                                                        <router-link
                                                            :to="{ name: 'user.timeline', params: { slug: like.user.id }}"
                                                            class="h6 post__author-name fn">
                                                            <img :src="like.user.image_thumbnail" :alt="like.user.name">
                                                        </router-link>
                                                    </li>
                                                    <li v-if="photo.number_of_likes > 6">
                                                        <a href="javascript:void(0)" class="all-users">
                                                            +{{ memberLength(photo.number_of_likes) }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="swiper-slide">
                                                <div class="friend-count" data-swiper-parallax="-500">
                                                    <a href="javascript:void(0)" class="friend-count-item"   >
                                                        <div class="h6" v-if="photo.media">{{ photo.media.length }}</div>
                                                        <div class="title">Photos</div>
                                                    </a>
                                                    <a href="javascript:void(0)" class="friend-count-item">
                                                        <div class="h6">{{ photo.number_of_comments }}</div>
                                                        <div class="title">Comments</div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-pagination"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="photo-album-wrapper" v-else>
                        <div class="page-description">
                            <div class="icon">
                                <svg class="olymp-star-icon left-menu-icon">
                                    <use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use>
                                </svg>
                            </div>
                            <span>{{ $t('messages.not_found_photo') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <action-detail
            :showAction.sync="showAction"
            :dataAction.sync="dataAction.list_action"
            :checkLikeActions.sync="dataAction.checkLikeAction"
            :canComment.sync="checkPermission"
            :roomLike="`campaign${pageId}`">
        </action-detail>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import axios from 'axios'
import ShowText from '../../libs/ShowText.vue'
import ActionDetail from '../../event/ActionDetail.vue'
import MasterLike from '../../like/MasterLike.vue'

export default {
    created() {
        this.getlistPhotos(this.pageId)
    },
    updated() {
        this.swiper()
    },
    data: () => ({
        showAction: false,
        dataAction: {},
        checkPermission: true
    }),
    computed: {
        ...mapState('campaign', [
            'listPhotos'
        ]),
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        }),
    },
    mounted() {
        $(window).scroll(() => {
            if ($(document).height() <= $(window).scrollTop() + $(window).height()) {
                this.loadMore()
            }
        })
    },
    methods: {
        ...mapActions('campaign', [
            'getlistPhotos',
            'loadMorePhotos'
        ]),
        ...mapActions('action', [
            'getDetailAction',
        ]),
        memberLength(members) {
            if (members > 6) {
                return members -6
            }

            return 0
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
                    autoHeight: $autoheight,
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
        },
        loadMore() {
            var data = {
                campaignId: this.pageId,
                currentPage: this.listPhotos.list_action.current_page,
                lastPage: this.listPhotos.list_action.last_page
            }

            this.loadMorePhotos(data)
        },
        beforeDestroy() {
            $(window).off()
        },
        detailAction(photoId) {
            this.showAction = true
            this.getDetailAction(photoId)
                .then(data => {
                    this.dataAction = data.actions
                    this.checkPermission = data.checkPermission
                })
                .catch(err => {
                    const message = this.$i18n.t('messages.message-fail')
                    noty({ text: message, force: true, container: false })
                })
        }
    },
    components: {
        ShowText,
        ActionDetail,
        MasterLike
    }
}
</script>
<style lang="scss">
    .friends-harmonic {
        img {
            height: 32px;
            width: 32px;
        }
    }
</style>
