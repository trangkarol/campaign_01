<template>
    <div class="ui-block">
        <article :class="{
            'no-image': !event.media.length,
            'hentry post has-post-thumbnail': true
        }">
            <div class="post__author author vcard inline-items">
                <img :src="owner.image_thumbnail" alt="author">
                <div class="author-date">
                    <router-link class="h6 post__author-name fn" :to="{ name: 'user.timeline', params: { slug: owner.slug }}">
                        {{ owner.name }}
                    </router-link>
                    {{ $t('homepage.create_a') }}
                    <span class="span-event">{{ $t('homepage.event') }}</span>
                    <router-link class="link-event" :to="{ name: 'event.index', params: {
                        slug: event.campaign_id,
                        slugEvent: event.slug
                    }}">
                        "<span class="title-event">{{ event.title }}</span>"
                    </router-link>
                    - {{ $t('homepage.in_campaign') }}
                    <router-link class="link-event" :to="{ name: 'campaign.timeline', params: { slug: event.campaign.slug }}">
                        "<span class="title-event">{{ event.campaign.title }}</span>"
                    </router-link>
                    <div class="post__date">
                        <time class="published">
                            <timeago :since="event.created_at"/>
                        </time>
                    </div>
                </div>
                <div class="more">
                    <i aria-hidden="true" class="fa fa-calendar-check-o"></i>
                </div>
            </div>
            <p>
                <show-text
                    :text="event.description"
                    :show_char=850
                    :number_char_show=700
                    :show="$t('events.show_more')"
                    :hide="$t('events.show_less')">
                </show-text>
            </p>
            <list-image v-if="event.media.length" :listImage="event.media" ></list-image>
        </article>
    </div>
</template>
<script>
    import { mapState, mapActions } from 'vuex'
    import ShowText from '../libs/ShowText.vue'
    import ListImage from './ListImage.vue'

    export default {
        props: {
            event: {},
            owner: {}
        },
        components: {
            ShowText,
            ListImage
        }
    }
</script>

<style lang="scss" scoped>
    .no-image {
        padding: 20px !important;
    }

    .post {
        padding-bottom: 0px;

        .post__author {
            margin-bottom: 10px;
        }

        .author-date {
            font-size: 14px;
            width: 82%;
        }
        .post-thumb {
            margin-top: 10px;
            margin-bottom: 0px;
        }
        .span-event{
            color: #fe5d39;
        }

        .author-date {
            font-size: 14px;
            .link-event {
                color: rgb(97, 99, 115);
                text-transform: uppercase;
                font-weight: 400;
                .title-event {
                    color: #616373;
                    &:hover {
                        color: #fe5d39;
                    }
                }
            }
            .published {
                font-size: 13px;
            }
        }

        .more {
            padding: 10px;
            padding-left: 11px;
            border-radius: 70%;
            border: 0;
            margin: -10px -10px 0px 0px;
            i {
            font-size: 30px;
                color: #404358;
            }
        }
    }
</style>
