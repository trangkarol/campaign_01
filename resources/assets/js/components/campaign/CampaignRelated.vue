<template lang="html">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="album-page" role="tabpanel">
                    <div class="photo-album-wrapper" v-if="campaignRelated.total > 0">

                        <div class="photo-album-item-wrap col-4-width" v-for="campaign in campaignRelated.data">
                            <div class="photo-album-item" data-mh="album-item">
                                <div class="photo-item" v-if="campaign.media != null">
                                    <router-link :to="{ name: 'campaign.timeline', params: { slug: campaign.id }}"
                                        class="h6 author-name">
                                        <img :src="campaign.media[0].image_small" alt="photo">
                                    </router-link>
                                    <div class="overlay overlay-dark"></div>

                                    <router-link
                                        :to="{ name: 'campaign.timeline', params: { slug: campaign.id }}"
                                        class="more">
                                        <svg class="olymp-three-dots-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                        </svg>
                                    </router-link>

                                    <master-like
                                        :likes="campaign.likes"
                                        :checkLiked="checkLiked"
                                        :flag="'campaign'"
                                        :type="'like'"
                                        :modelId="campaign.id"
                                        :numberOfComments="campaign.number_of_comments"
                                        :numberOfLikes="campaign.number_of_likes"
                                        :class="'post-add-icon'"
                                        :showMore="false">
                                    </master-like>
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#open-photo-popup-v2" class="full-block"></a>
                                </div>

                                <div class="content">
                                    <p>{{ campaign.title }}</p>

                                    <span class="sub-title">{{ timeAgo(campaign.created_at) }}</span>

                                    <div class="swiper-container" data-slide="fade">
                                        <div class="swiper-wrapper">
                                            <div class="swiper-slide">
                                                <ul class="friends-harmonic" >
                                                    <li v-for="(like, index) in campaign.likes" v-if="index <= 6">
                                                        <router-link
                                                            :to="{ name: 'user.timeline', params: { slug: like.user.id }}"
                                                            class="h6 post__author-name fn">
                                                            <img :src="like.user.image_thumbnail" :alt="like.user.name">
                                                        </router-link>
                                                    </li>
                                                    <li v-if="campaign.number_of_likes > 0">
                                                        <a href="javascript:void(0)" class="all-users">+{{ memberLength(campaign.number_of_likes) }}</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="swiper-slide">
                                                <div class="friend-count" data-swiper-parallax="-500">
                                                    <a href="javascript:void(0)" class="friend-count-item"   >
                                                        <div class="h6">{{ campaign.number_of_likes }}</div>
                                                        <div class="title">Likes</div>
                                                    </a>
                                                    <a href="javascript:void(0)" class="friend-count-item" v-if="campaign.users != null">
                                                        <div class="h6">{{ campaign.users.length }}</div>
                                                        <div class="title">Members</div>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="swiper-slide">
                                               <div class="friend-count" data-swiper-parallax="-500">
                                                    <a href="javascript:void(0)"
                                                        @click="joinCampaign(campaign.id)"
                                                        id="dssd"
                                                        class="btn btn-md-2 btn-border-think custom-color c-grey">
                                                        {{ $t('campaigns.join-now') }}</a>
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
                            {{ $t('messages.not_found_campaign') }}
                        </h2>
                    </div>
                </div>
            </div>

        </div>
        <!-- form comfirm join campaign -->
        <modal :show.sync="flagComfirm">
            <h5 class="exclamation-header" slot="header">
                {{ $t('messages.comfirm-join-campaign') }}
            </h5>
            <div class="body-modal" slot="main">
                <a href="javascript:void(0)"
                    class="btn btn-breez col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="joinCampaigns">
                    {{ $t('form.button.agree') }}
                </a>
                <a href="javascript:void(0)"
                    class="btn btn-secondary col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="cancelJoinCampaign">
                    {{ $t('form.button.cancel') }}
                </a>
            </div>
        </modal>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { get, post } from '../../helpers/api'
import ShowText from '../libs/ShowText.vue'
import noty from '../../helpers/noty'
import Modal from '../libs/Modal.vue'
import MasterLike from '../like/MasterLike.vue'

export default {
    data() {
        return {
            campaignRelated: [],
            checkLiked: [],
            flagComfirm: false,
            campaignIdCurrent: 0
        }
    },
    created() {
        this.getCampaignRelated(this.pageId)
    },
    updated() {
        this.swiper()
    },
    methods: {
        ...mapActions('campaign', [
            'attendCampaign'
        ]),
        getCampaignRelated(campaignId) {
            get(`campaign/campaign-related/${campaignId}`)
                .then(res => {
                    console.log('campaign-related', res.data.campaign_related)
                    this.campaignRelated = res.data.campaign_related.campaign
                    this.checkLiked = res.data.campaign_related.checkLiked
                })
                .catch(err => {
                    //
                })
        },
        joinCampaign(campaignId) {
            this.flagComfirm = true
            this.campaignIdCurrent = campaignId
        },
        cancelJoinCampaign() {
            this.flagComfirm = false
        },
        cancel() {
            this.flagComfirm = false
        },
        timeAgo(time) {
            return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
        },
        memberLength(members) {
            if (members > 6) {
                return members -6
            }

            return 0
        },
        joinCampaigns() {
            let data = {
                campaignId: this.campaignIdCurrent,
                flag: 'join'
            }
            this.attendCampaign(data)
                .then(status => {
                    this.flagComfirm = false
                    let campaign = this.campaignRelated.data
                    campaign = $.grep(campaign, function(item){
                        return item.id != data.campaignId;
                    });

                    this.campaignRelated.data = campaign

                    const message = this.$i18n.t('messages.join_campaign_success')
                    noty({ text: message, force: true, type: 'success', container: false })
                })
                .catch(err => {
                    console.log(err)
                    this.flagComfirm = false
                    const message = this.$i18n.t('messages.join_campaign_fail')
                    noty({ text: message, force: true, container: false })
                })
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
                    loop: false,
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
        }
    },
    components: {
        ShowText,
        Modal,
        MasterLike
    }
}
</script>
<style lang="scss">
</style>
