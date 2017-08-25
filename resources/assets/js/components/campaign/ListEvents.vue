<template lang="html">
    <div id="newsfeed-items-grid" v-if="events != null">
        <div class="ui-block" v-for="event in events.data">
            <article class="hentry post">
                <div class="post__author author vcard inline-items" v-if="event.media != null">
                    <router-link :to="{ name: 'event.index', params: { slug: event.slug }}">
                        <img :src="event.media[0].image_thumbnail" :alt="event.name">
                    </router-link>
                    <div class="author-date">
                        <router-link
                            :to="{ name: 'event.index', params: { slug: event.slug }}">
                            {{ event.title }}
                        </router-link>

                        <div class="post__date">
                            <timeago
                                :max-time="86400 * 365"
                                class="published date-format"
                                :since="event.created_at">
                            </timeago>
                        </div>
                    </div>

                    <div class="more" v-if="checkPermission"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                        <ul class="more-dropdown">
                            <li>
                                <a href="javascript:void(0)">{{ $t('events.edit-event') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0)">{{ $t('events.delete-event') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
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
                    >
                </master-like>

                <div class="control-block-button post-control-button">
                    <master-like
                        :likes="event.likes"
                        :checkLiked="checkLiked"
                        :flag="model"
                        :type="'like-infor'"
                        :modelId="event.id"
                        :numberOfComments="event.number_of_comments"
                        :numberOfLikes="event.number_of_likes">
                    </master-like>

                    <a href="javascript:void(0)" class="btn btn-control">
                        <svg class="olymp-comments-post-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-comments-post-icon"></use></svg>
                    </a>

                    <a href="javascript:void(0)" class="btn btn-control">
                        <svg class="olymp-share-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use></svg>
                    </a>

                </div>
            </article>

            <comment
                :comments="event.comments"
                :numberOfComments="event.number_of_comments"
                :model-id ="event.id"
                :flag="model"
                :classListComment="''"
                :classFormComment="''">
            </comment>
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import axios from 'axios'
import Comment from '../comment/Comment.vue'
import ShowText from '../libs/ShowText.vue'
import MasterLike from '../like/MasterLike.vue'

export default {
    data: () => ({
        model: 'event',
        flagComments: []
    }),
    computed: {
        ...mapGetters('campaign', [
            'checkPermission',
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
    components: {
        Comment,
        ShowText,
        MasterLike
    }
}
</script>

<style lang="scss">
    .date-format {
        font-size: 13px;
        color: #bbb;
    }
</style>
