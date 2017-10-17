<template>
    <div class="ui-block">
        <div class="modal-dialog ui-block window-popup event-private-public public-event">
            <article class="hentry post has-post-thumbnail thumb-full-width private-event">
                <div class="private-event-head inline-items">
                    <img src="/images/avatar77-sm.jpg" alt="author">
                    <div class="author-date">
                        <router-link class="h3 event-title" :to="{ name: 'campaign.timeline', params: { slug: campaign.slug }}">
                            <span v-if="campaign.title.length < 51">{{ campaign.title }}</span>
                            <span v-else>{{ campaign.title.substr(0, 50) }}...</span>
                        </router-link>
                        <div class="event__date">
                            <time class="published">
                                <timeago :since="campaign.created_at"/>
                            </time>
                        </div>
                    </div>
                </div>
                <div class="post-thumb">
                    <img :src="campaign.media[0].image_default" alt="photo">
                </div>
                <div class="row">
                    <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12">
                        <div class="post__author author vcard inline-items">
                            <img :src="owner.image_thumbnail" alt="author">
                            <div class="author-date">
                                <router-link class="h6 post__author-name fn" :to="{ name: 'user.timeline', params: { slug: owner.slug }}">
                                    {{ owner.name }}
                                </router-link>
                                {{ $t('homepage.create_a') }}
                                <router-link :to="{ name: 'campaign.timeline', params: { slug: campaign.slug }}">
                                    {{ $t('homepage.new_campaign') }}
                                </router-link>
                            </div>
                        </div>
                        <p v-if="campaign.description.length < 271" v-html="campaign.description"></p>
                        <p v-else v-html="campaign.description.substr(0, 270) + '...'"></p>
                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                        <div class="event-description">
                            <h6 class="event-description-title">{{ $t('homepage.information') }}</h6>
                            <div class="place inline-items">
                                <i class="fa fa-location-arrow" aria-hidden="true"></i>
                                <div>
                                    <span>{{ $t('homepage.address') + ': ' + campaign.address }}</span>
                                </div>
                            </div>

                             <div class="place inline-items">
                                <i class="fa fa-hashtag" aria-hidden="true"></i>
                                <div><span>Hashtag: {{ campaign.hashtag }}</span></div>
                            </div>

                            <div class="place inline-items">
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <div><span>{{ $t('homepage.start_day') }}: <timeago :since="campaign.created_at"/></span></div>
                            </div>

                            <router-link class="btn bg-primary btn-sm full-width"
                                :to="{ name: 'campaign.timeline', params: { slug: campaign.slug }}">
                                {{ $t('homepage.see_details') }}
                            </router-link>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</template>
<script>
   import { mapState, mapActions } from 'vuex'

   export default {
        props: {
            campaign: {},
            owner: {}
        }
    }
</script>
<style lang="scss" scoped>
    .blog-post-v3 {
        .post-content {
            width: 50%;
            padding: 0px 10px 0px 30px;
        }
        .post-category {
            padding: 0px;
        }
        .span-tags {
            display: inline-block;
            background: #00b7ff;
            padding: 5px;
            border-radius: 3px;
            margin: 0px 1px 3px 1px;
            &:first-child {
                border-radius: 0px 3px 3px 0px;
            }
        }
    }
    .author-date {
        font-size: 14px;
        .published {
            font-size: 13px;
        }
    }
    .modal-dialog {
        margin-top: 20px;

        .post.thumb-full-width .post-thumb {
            border-radius: 0;
            margin: 25px -25px 25px -25px;
        }

        .post .author-date {
            width: 81% !important;
            margin-left: 0px;
        }

        .event-description {
            margin-bottom: 0px;
            margin-left: -10px;
        }

        .event-description .event-description-title {
            font-weight: 700;
            margin-bottom: 15px;
        }

        .post {
            position: relative;
            padding: 15px 25px 10px;
            border-bottom: 0px;

            .btn {
                margin-bottom: 15px;
                margin-top: 10px;
            }
        }

        .place {
            margin-bottom: 15px;

            &::last-child {
                margin-bottom: 20px;
            }

            i {
                font-size: 14px;
                margin-right: 10px;
                margin-left: 3px;
            }

            .fa-hashtag {
                font-size: 13px;
            }

            > div {
                display: inline-block;
                width: 80%;
            }
        }
        .private-event-head {
            margin-bottom: -10px;
            .author-date {
                width: 89% !important;
            }
            .more {
                margin-top: 5px;
                fill: #404358;
            }
        }
    }
</style>
