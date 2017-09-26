<template>
    <div class="follow">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="ui-block">
                        <div class="ui-block-title">
                            <div class="h6 title">{{ $t('user.friend.friends') }}</div>
                            <form class="w-search">
                                <div class="form-group with-button">
                                    <input class="form-control" v-model="searchKey" @input="search" type="text" :placeholder="$t('user.friend.search')">
                                    <button @click.prevent="search">
                                        <svg class="olymp-magnifying-glass-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-magnifying-glass-icon"></use>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Friends -->
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-6" v-for="(user, index) in friends">
                    <div class="ui-block animated zoomIn">
                        <div class="friend-item">
                            <div class="friend-header-thumb">
                                <img :src="user.default_header" alt="friend">
                            </div>
                            <div class="friend-item-content">
                                <div class="friend-avatar">
                                    <div class="author-thumb">
                                        <img :src="user.image_small" alt="author">
                                    </div>
                                    <div class="author-content">
                                        <router-link :to="{ name: 'user.timeline', params: { slug: user.slug } }" class="h5 author-name">
                                            {{ user.name }}
                                        </router-link>
                                        <div class="country">{{ user.address }}</div>
                                    </div>
                                </div>
                                <div class="swiper-container" ref="swiper" data-slide="fade">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="friend-count" data-swiper-parallax="-500">
                                                <a class="friend-count-item">
                                                    <div class="h6">{{ user.sender_count + user.recipient_count}}</div>
                                                    <div class="title">{{ $t('user.friend.friends') }}</div>
                                                </a>
                                                <a class="friend-count-item">
                                                    <div class="h6">{{ user.photos_count }}</div>
                                                    <div class="title">{{ $t('user.friend.photo') }}</div>
                                                </a>
                                                <a class="friend-count-item">
                                                    <div class="h6">{{ user.videos_count }}</div>
                                                    <div class="title">{{ $t('user.friend.video') }}</div>
                                                </a>
                                            </div>
                                            <div class="control-block-button" data-swiper-parallax="-100">
                                                <a class="btn btn-control" :class="{ 'bg-blue': user.is_friend, 'bg-gray': !user.is_friend }">
                                                    <svg class="olymp-happy-face-icon">
                                                        <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                                                    </svg>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="btn btn-control bg-purple"
                                                    @click="addChatComponent(user)">
                                                    <svg class="olymp-chat---messages-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-chat---messages-icon"></use></svg>
                                                    <div class="ripple-container"></div>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <p class="friend-about" data-swiper-parallax="-500">
                                                {{ $t('user.friend.birthday') }}: {{ user.birthday }}<br>
                                                {{ $t('user.friend.gender') }}: {{ (user.gender) ? $t('user.friend.male') : $t('user.friend.female') }}<br>
                                                {{ $t('user.friend.email') }}: {{ slipEmail(user.email) }}<br>
                                                {{ $t('user.friend.phone') }}: {{ user.phone }}<br>
                                            </p>
                                            <div class="friend-since" data-swiper-parallax="-100">
                                                <h6>{{ $t('user.friend.follow_since') }}:</h6>
                                                <div class="h6">{{ user.pivot.created_at }}</div>
                                            </div>
                                        </div>
                                    </div>
                                   <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="spinner-load-more" v-if="loading">
            <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>
            <span>{{ $t('user.friend.loading') }}</span>
        </div>
        <h4 class="text-center" v-show="(!friends.length && searchKey) || (!hasData && !friends.length)">Nothing found</h4>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import DatePicker from '../libs/DatePicker.vue'
    import Modal from '../libs/Modal.vue'
    import noty from '../../helpers/noty'
    import { get, patch, post } from '../../helpers/api'
    import Noty from 'noty'
    import { EventBus } from '../../EventBus.js'
    export default {
        data: () => ({
            searchKey: '',
            friends: [],
            page: 0,
            hasData: true,
            loading: false,
        }),
        created() {
            this.listUser();
        },
        mounted() {
            $(window).scroll(() => {
                if (this.hasData && !this.searchKey) {
                    if ($(document).height() - $(window).height() < $(window).scrollTop() + 1) {
                        this.listUser()
                    }
                }
            })
        },
        updated() {
            this.swiper()
        },
        computed: {
            ...mapState('auth', { authUser: 'user' })
        },
        beforeDestroy() {
            $(window).off()
        },
        methods: {
            slipEmail(email) {
                if (email.length > 25) {
                    return email.substring(0, 22) + '...'
                }

                return email
            },
            listUser() {
                this.page++
                this.loading = true
                get(`user/${this.pageId}/friends/${this.page}`)
                    .then(res => {
                        this.loading = false
                        if(!res.data.data.length)
                            this.hasData = false
                        this.friends = this.friends.concat(res.data.data)
                    })
            },
            search: _.debounce(function () {
                if (!this.searchKey.trim()) {
                    this.page = 0
                    this.hasData = true
                    this.listUser()
                } else {
                    this.loading = true
                    get(`user/${this.pageId}/search-friends/${this.searchKey}`)
                        .then(response => {
                            this.friends = response.data.data
                            this.loading = false
                        })
                        .catch(function(error) {
                            noty({
                                text: this.$i18n.t('messages.load_fail'),
                                type: 'error',
                                force: false,
                                container: false
                            })
                        });
                }
            }, 500),
            follow(event, id) {
                patch('follow/' + id)
                    .then(response => {
                        if (event.target.textContent == this.$i18n.t('user.follow.follow')) {
                            event.target.innerHTML = this.$i18n.t('user.follow.unfollow')
                            $('.link-svg' + id).removeClass('red')
                            $('.link-svg' + id + '> svg').attr('class', 'olymp-check-icon');
                            $('.link-svg' + id + '> svg > use').attr('xlink:href', '/frontend/icons/icons.svg#olymp-check-icon')
                        } else {
                            event.target.innerHTML = this.$i18n.t('user.follow.follow')
                            $('.link-svg' + id).addClass('red')
                            $('.link-svg' + id + '> svg').attr('class', 'olymp-close-icon')
                            $('.link-svg' + id + '> svg > use').attr('xlink:href', '/frontend/icons/icons.svg#olymp-close-icon')
                        }
                    })
                    .catch(function(error) {
                        noty({
                            text: this.$i18n.t('messages.load_fail'),
                            type: 'error',
                            force: false,
                            container: false
                        })
                    });
            },
            modal(text, callback) {
                let n = new Noty({
                    text: text,
                    modal: true,
                    layout: 'center',
                    closeWith: 'button',
                    buttons: [
                        Noty.button(this.$t('events.donation.yes'), 'btn-upper btn btn-primary btn--half-width', () => {
                            callback()
                            n.close()
                        }),
                        Noty.button(this.$t('events.donation.no'), 'btn-upper btn btn-secondary btn--half-width', () => {
                            n.close()
                        })
                    ]
                }).show();
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
            addChatComponent(user) {
                EventBus.$emit('addChatComponent', {
                    id: user.id,
                    name: user.name,
                    singleChat: true,
                    slug: this.replaceSpace(user.name + '-' + user.id)
                })
            },
            replaceSpace(str) {
                return str.replace(' ', '-').toLowerCase()
            }
        }
    }
</script>

<style lang="scss" scoped>
    .friend-avatar {
        .author-thumb {
            >img {
                width: 92px;
                height: 92px;
            }
        }
    }

    .load-follower {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 3;
        background: url(/images/loading.gif) 50% 10% no-repeat rgba(235, 242, 242, 0.66);
        opacity: .6;
        background-size: 120px 120px;
    }

    .spinner-load-more {
        text-align: center;
    }

    .follow {
        margin-bottom: 25px;
        position: relative;
    }

    .friend-avatar {
        margin-bottom: 20px;
    }

    .swiper-container {
        padding-bottom: 30px;
    }

    .spinner-search {
        float: right;
        margin-right: 10px;
    }

    .bg-gray {
        background-color: darkgray;
    }

    .btn-control {
        border-radius: 100%;
        width: 50px;
        height: 50px;
        line-height: 54px;
        padding: 0;
        font-size: 26px;
        fill: #fff;
    }

    [class^="olymp-"], [class*=" -icon"] {
        height: 22px;
        width: 22px;
    }

    .red {
        background: #f97070 !important;
    }

    .more-dropdown > li > a {
        color: #515365 !important;
        &:hover {
            color: #fc5c3a !important;
        }
    }
</style>
