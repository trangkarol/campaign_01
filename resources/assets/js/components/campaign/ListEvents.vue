<template lang="html">
    <div ref="scrollContainer" id ="data-loadmore">
        <div id="newsfeed-items-grid" v-if="events.total > 0">
            <div class="ui-block" v-for="event in events.data">
                <article class="hentry post has-post-thumbnail thumb-full-width">

                    <div class="post__author author vcard inline-items" v-if="event.user">
                        <router-link :to="{ name: 'user.timeline', params: { slug: event.user.slug }}">
                            <img :src="event.user.image_thumbnail" :alt="event.user.name">
                        </router-link>

                        <div class="author-date">
                            <router-link :to="{ name: 'user.timeline', params: { slug: event.user.slug }}"
                                class="h6 post__author-name fn">
                                {{ event.user.name }}
                            </router-link>
                            <div class="post__date">
                                {{ timeAgo(event.created_at) }}
                            </div>
                        </div>
                        <div class="more">
                            <i aria-hidden="true" class="fa fa-calendar-check-o"></i>
                        </div>
                    </div>
                    <list-image v-if="event.media.length" :listImage="event.media" ></list-image>
                    <router-link :to="{ name: 'event.index', params: { slugEvent: event.slug }}"
                        class="h2 post-title">
                        {{ event.title }}
                    </router-link>

                    <show-text
                        :type="false"
                        :text="event.description"
                        :show_char=300
                        :show="$t('events.show_more')"
                        :hide="$t('events.show_less')"
                        :number_char_show=200>
                    </show-text>

                    <master-like
                        :likes="event.likes"
                        :checkLiked="checkLiked"
                        :flag="model"
                        :type="'like'"
                        :modelId="event.id"
                        :numberOfComments="event.number_of_comments"
                        :numberOfLikes="event.number_of_likes"
                        :showMore="true"
                        :deleteDate="event.deleted_at"
                        :roomLike="`campaign${pageId}`">
                    </master-like>

                    <div class="control-block-button post-control-button">
                        <master-like
                            :likes="event.likes"
                            :checkLiked="checkLiked"
                            :flag="model"
                            :type="'like-infor'"
                            :modelId="event.id"
                            :numberOfComments="event.number_of_comments"
                            :numberOfLikes="event.number_of_likes"
                            :deleteDate="event.deleted_at"
                            :roomLike="`campaign${pageId}`">
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
                <comment
                    :comments="event.comments"
                    :numberOfComments="event.number_of_comments"
                    :model-id ="event.id"
                    :flag="model"
                    :classListComment="''"
                    :classFormComment="''"
                    :deleteDate="event.deleted_at"
                    :canComment="checkComemnt()"
                    :roomLike="`campaign${pageId}`">
                </comment>
            </div>
        </div>
        <div class="page-description" v-else>
            <div class="icon">
                <svg class="olymp-star-icon left-menu-icon">
                    <use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use>
                </svg>
            </div>
            <span>{{ $t('messages.no_event_show') }}</span>
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'
import axios from 'axios'
import Comment from '../comment/Comment.vue'
import ShowText from '../libs/ShowText.vue'
import MasterLike from '../like/MasterLike.vue'
import ListImage from '../home/ListImage.vue'
import ShareSocialNetwork from '../libs/ShareSocialNetwork.vue'
import PluginSidebar from '../libs/PluginSidebar.vue'

export default {
    data()
    {
        return {
            model: 'event',
            flagComments: [],
            roomLike: `campaign${this.pageId}`
        }
    },
    computed: {
        ...mapGetters('campaign', [
            'checkPermission',
            'checkJoinCampaign',
        ]),
        ...mapState('campaign', [
            'campaign',
            'events',
            'loading',
            'checkLiked'
        ]),
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        })
    },
    methods: {
        ...mapActions('campaign', [
            'updateEventsCampaign',
        ]),
        ...mapActions('like', [
            'appendLike',
        ]),
        timeAgo(time) {
            return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
        },
        checkComemnt() {
            if (this.campaign.status) {
                if ((this.campaign.status['value'] == 0 && this.checkJoinCampaign == 3)
                    || (this.campaign.status['value'] == 1))  {
                    return true;
                }

                return false;
            }
        }
    },
    components: {
        Comment,
        ShowText,
        MasterLike,
        ListImage,
        ShareSocialNetwork,
        PluginSidebar
    },
    sockets: {
        createEventSuccess: function (data) {
            const event = data.data
            const eventAdd = {
                address: event.info.address,
                campaign_id: Number(event.info.campaign_id),
                comments: [],
                likes: [],
                created_at: event.info.created_at,
                deleted_at: null,
                description: event.info.description,
                id: event.info.id,
                latitude: event.info.latitude,
                longitude: event.info.longitude,
                number_of_comments: 0,
                number_of_likes: 0,
                slug: event.info.slug,
                timeAgo: event.info.timeAgo,
                title: event.info.title,
                updated_at: event.info.updated_at,
                user_id: Number(event.user.id),
                user: event.user,
                media: event.info.media
            }

            this.updateEventsCampaign(eventAdd)
        },
        newLike: function (data) {
            if (this.user.id != data.user.id) {
                this.appendLike(data)
            }
        }
    }
}
</script>

<style lang="scss" scoped>
    .date-format {
        font-size: 13px;
        color: #bbb;
    }

    .post {
        padding: 25px 25px 0px 25px;
        border-bottom: 0px;
        .post-additional-info {
            margin-bottom: 20px;
        }

        .more {
            padding: 10px;
            padding-left: 11px;
            border-radius: 70%;
            border: 0;
            margin: -10px 0px 0px 0px;
            i {
            font-size: 30px;
                color: #404358;
            }
        }
    }
</style>
