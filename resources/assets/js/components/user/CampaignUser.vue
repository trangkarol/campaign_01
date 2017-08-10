<template lang="html">
<div>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="clients-grid">

                    <ul class="cat-list-bg-style align-center sorting-menu">
                        <router-link
                            class="cat-list__item"
                            :to="{ name: 'user.campaign', params: { slug: $route.params.slug, path: 'following-campaign' }}"
                            active-class="active"
                            tag="li">
                            <a href="">{{ $t('user.label.following_campaign') }}</a>
                        </router-link>

                        <router-link
                            class="cat-list__item"
                            :to="{ name: 'user.campaign', params: { slug: $route.params.slug, path: 'joined-campaign' }}"
                            active-class="active"
                            tag="li">
                            <a href="">{{ $t('user.label.joined_campaign') }}</a>
                        </router-link>
                        <router-link
                            class="cat-list__item"
                            :to="{ name: 'user.campaign', params: { slug: $route.params.slug, path: 'owned-campaign' }}"
                            active-class="active"
                            tag="li">
                            <a href="">{{ $t('user.label.owned_campaign') }}</a>
                        </router-link>
                    </ul>
                </div>
            </div>

            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-xs-12" v-for="campaign in data">
                <div class="ui-block">
                    <article class="hentry post has-post-thumbnail thumb-full-width">

                        <div class="post__author author vcard inline-items">
                            <img :src="campaign.owner[0].image_thumbnail" alt="author">

                            <div class="author-date">
                                <a class="h6 post__author-name fn" href="02-ProfilePage.html">{{ campaign.owner[0].name }}</a> {{ $t('user.quote.create_campaign') }}
                                <div class="post__date">
                                    <time class="published" datetime="2017-03-24T18:18">
                                        {{ campaign.created_at }}
                                    </time>
                                </div>
                            </div>

                            <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                <ul class="more-dropdown">
                                    <li>
                                        <a href="#">Edit Post</a>
                                    </li>
                                    <li>
                                        <a href="#">Delete Post</a>
                                    </li>
                                    <li>
                                        <a href="#">Turn Off Notifications</a>
                                    </li>
                                    <li>
                                        <a href="#">Select as Featured</a>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <div class="post-thumb">
                            <img :src="campaign.media[0].image_default" alt="photo">
                        </div>

                        <router-link :to="{ name: 'campaign.timeline', params: { slug: campaign.slug }}"  class="h2 post-title">{{ shorten(campaign.title, 40) }}</router-link>

                        <p>{{ shorten(strip(campaign.description), 200) }}</p>

                        <router-link :to="{ name: 'campaign.timeline', params: { slug: campaign.slug }}" class="btn btn-md-2 btn-border-think c-grey btn-transparent custom-color">{{ $t('user.button.read_more') }}</router-link>


                        <div class="control-block-button post-control-button">

                            <master-like
                                :likes="campaign.likes"
                                :checkLiked="checkLiked"
                                :flag="'campaign'"
                                :type="'like-infor'"
                                :modelId="campaign.id"
                                :numberOfComments="campaign.number_of_comments"
                                :numberOfLikes="campaign.number_of_likes"
                                >
                            </master-like>

                            <a href="#" class="btn btn-control">
                                <svg class="olymp-comments-post-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-comments-post-icon"></use></svg>
                            </a>

                            <a href="#" class="btn btn-control">
                                <svg class="olymp-share-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use></svg>
                            </a>

                        </div>

                    </article>
                </div>
            </div>
        </div>
        <div>
            <img src="/images/load.gif" class="center" v-if="hasData">
            <h5 class="text-center" v-else>{{ $t('user.quote.no_data') }}</h5>
        </div>
    </div>
</div>
</template>

<script>
import axios from 'axios'
import MasterLike from '../like/MasterLike.vue'

export default {
    data() {
        return {
            data: [],
            page: 0,
            hasData: true,
            loading: false,
            checkLiked: [],
        }
    },
    created() {
        this.fetchData()
    },
    components: {
        MasterLike
    },
    mounted() {
        $(window).scroll(() => {
             if ($(document).height() <= $(window).scrollTop() + $(window).height() && this.hasData) {
                 this.fetchData()
             }
         })
    },
    watch: {
        '$route': 'changRoute'
    },
    methods: {
        fetchData() {
            this.page++
            this.loading = true
            axios.get(`/api/user/${this.pageId}/${this.$route.params.path}/?page=${this.page}`)
                .then(res => {
                    if (!res.data.data.campaign.data.length) {
                        this.hasData = false
                    }
                    this.data = this.data.concat(res.data.data.campaign.data)
                    this.checkLiked = res.data.data.checkLiked
                })
            this.loading = false
        },
        changRoute() {
            this.hasData = true
            this.data = []
            this.page = 0
            this.fetchData()
        },
        shorten(string, length) {
            if (string.length > length) {
                return string.substring(0, length - 3) + '...'
            }
            return string
        },
        strip(html) {
           let tmp = document.createElement("DIV")
           tmp.innerHTML = html
           return tmp.textContent || tmp.innerText || ""
        }
    },
}
</script>

<style lang="scss" scoped>
    .center {
        display: block;
        margin: 0 auto;
    }
</style>
