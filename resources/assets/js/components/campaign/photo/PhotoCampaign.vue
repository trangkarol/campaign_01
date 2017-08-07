<template lang="html">
    <div class="row">
       <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="album-page" role="tabpanel">

                    <div class="photo-album-wrapper" v-if="listPhotos.total > 0">

                        <div class="photo-album-item-wrap col-4-width" v-for="photo in listPhotos.data">
                            <div class="photo-album-item" data-mh="album-item">
                                <div class="photo-item" v-if="photo.media.length > 0">

                                    <a href="javascript:void(0)">
                                        <img :src="photo.media[0].image_small" alt="photo">
                                    </a>

                                    <div class="overlay overlay-dark"></div>
                                    <a href="javascript:void(0)" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
                                    <a href="javascript:void(0)" class="post-add-icon">
                                        <svg class="olymp-heart-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use></svg>
                                        <span >{{ photo.likes.length }}</span>
                                    </a>
                                    <a href="javascript:void(0)"
                                        data-toggle="modal"
                                        data-target="#open-photo-popup-v2"
                                        class="full-block"
                                        @click="detailAction(photo)">
                                    </a>
                                </div>

                                <div class="content">
                                    <show-text
                                        :text="photo.caption"
                                        :show_char=70
                                        :show="$t('events.show_more')"
                                        :hide="$t('events.show_less')"
                                        class="title p">
                                    </show-text>

                                    <span class="sub-title">{{ timeAgo(photo.created_at) }}</span>

                                    <div class="swiper-container" data-slide="fade">
                                        <div class="swiper-wrapper">
                                            <div class="swiper-slide">
                                                <ul class="friends-harmonic" >
                                                    <li v-for="(like, index) in photo.likes" v-if="index <= 6">
                                                        <router-link
                                                            :to="{ name: 'user.timeline', params: { id: like.user.id }}"
                                                            class="h6 post__author-name fn">
                                                            <img :src="like.user.image_thumbnail" :alt="like.user.name">
                                                        </router-link>
                                                    </li>
                                                    <li v-if="memberLength(photo.likes) > 0">
                                                        <a href="#" class="all-users">+{{ memberLength(photo.likes) }}</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="swiper-slide">
                                                <div class="friend-count" data-swiper-parallax="-500">
                                                    <a href="javascript:void(0)" class="friend-count-item"   >
                                                        <div class="h6">{{ photo.media.length }}</div>
                                                        <div class="title">Photos</div>
                                                    </a>
                                                    <a href="javascript:void(0)" class="friend-count-item">
                                                        <div class="h6">{{ photo.comments.total }}</div>
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
                    <div class="empty center-block" v-else>
                        <h2>
                            {{ $t('events.not_found_action') }}
                        </h2>
                    </div>
                </div>
            </div>

        </div>
        <action-detail :showAction.sync = "showAction" :dataAction = "dataAction"></action-detail>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import axios from 'axios'
import ShowText from '../../libs/ShowText.vue'
import ActionDetail from '../../event/ActionDetail.vue'

export default {
    created() {
        this.getlistPhotos(this.$route.params.id)
    },
    updated() {
        this.swiper()
    },
    data: () => ({
        showAction: false,
        dataAction: {}
    }),
    computed: {
        ...mapState('campaign', [ 'listPhotos']),
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
        ...mapActions('campaign', ['getlistPhotos', 'loadMorePhotos']),
        timeAgo(time) {
            return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
        },
        memberLength(members) {
            if (members.length > 6) {
                return members.length -6
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
                campaignId: this.$route.params.id,
                currentPage: this.listPhotos.current_page,
                lastPage: this.listPhotos.last_page
            }

            this.loadMorePhotos(data)
        },
        detailAction(data) {
            this.showAction = true
            this.dataAction = data
        }
    },
    components: {
        ShowText,
        ActionDetail
    }
}
</script>
<style lang="scss">
</style>
