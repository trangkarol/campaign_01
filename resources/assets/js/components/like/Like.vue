<template lang="html">
    <div class="post-additional-info inline-items">
        <span>
            <a href="javascript:void(0)"
                class="post-add-icon inline-items"
                @click="likeActivity({
                    model: flag,
                    modelId: modelId,
                    user: user,
                    flag: flag,
                    numberOfLikes: like[flag][modelId].numberOfLikes
                })">
                <svg class="olymp-heart-icon">
                     <use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"
                        :class="{ fill: checkLike[flag][modelId] }">
                    </use>
                </svg>
            </a>
            <span class="span-show-name" @click="showMembersLiked()">
                <ul class="show-name" v-if="like[flag][modelId].numberOfLikes > 0">
                    <li v-for="(item, index) in like[flag][modelId]" v-if="index < 4">
                        <pre>{{ item.user.name }}</pre>
                    </li>
                    <li v-if="like[flag][modelId].numberOfLikes >= 4">
                        <pre>{{ $t('post.like.and') }} {{ like[flag][modelId].numberOfLikes - 4 }} {{ $t('post.like.more_like') }}</pre>
                    </li>
                </ul>
                {{ like[flag][modelId].numberOfLikes }}
            </span>
        </span>

        <ul class="friends-harmonic" v-if="like[flag][modelId].numberOfLikes > 0 && showMore">
            <li v-for="(item, index) in like[flag][modelId]" v-if="index < 4">
                <router-link
                    :to="{ name: 'user.timeline', params: { id: item.user.id }}"
                    class="h6 post__author-name fn">
                    <img :src="item.user.image_thumbnail" :alt="item.user.name">
                </router-link>
            </li>

            <li v-if="like[flag][modelId].numberOfLikes >= 4">
                <a href="#" class="all-users">+{{ like[flag][modelId].numberOfLikes - 4 }}</a>
            </li>
        </ul>

        <div class="names-people-likes" v-if="like[flag][modelId].numberOfLikes == 0 && showMore">
            {{ $t('post.like.like_first') }}
        </div>

        <div class="names-people-likes" v-else-if="showMore">
            <a href="javascript:void(0)" v-for="(item, index) in like[flag][modelId]" v-if="index < 2">
                {{ item.user.name }}
                <span v-if="(like[flag][modelId].numberOfLikes <=2
                    && like[flag][modelId].numberOfLikes > 1) || index == 1">
                    {{ $t('post.like.and') }}
                </span>
                <span v-if="like[flag][modelId].numberOfLikes >= 2 && index < 1"> , </span>
            </a>
            <a href="javascript:void(0)" >
                <span v-if="like[flag][modelId].numberOfLikes <= 2">
                    {{ $t('post.like.liked_this') }}
                </span>
                <span v-if="like[flag][modelId].numberOfLikes >= 3" @click="showMembersLiked()" class="more-like">
                    {{ $t('post.like.more_like') }}
                </span>
            </a>
        </div>
        <div v-if="showMore" :class="{ 'mrg-top': (like[flag][modelId].numberOfLikes < 3), 'comments-shared': true }">
            <a href="javascript:void(0)" class="post-add-icon inline-items">
                <svg class="olymp-speech-balloon-icon">
                    <use xlink:href="/frontend/icons/icons.svg#olymp-speech-balloon-icon"></use>
                </svg>
                <span>{{ commentTotal[flag][modelId] }}</span>
            </a>
            <a href="javascript:void(0)" class="post-add-icon inline-items">
                <svg class="olymp-share-icon">
                    <use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use>
                </svg>
                <span>0</span>
            </a>

        </div>

        <list-member-liked
            :modelId="modelId"
            :flag="flag"
            :flagShowListMember.sync="flagShowListMember">
        </list-member-liked>
    </div>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import ListMemberLiked from './ListMemberLiked.vue'

export default {
    data: () =>({
        flagShowListMember: false
    }),
    props: {
        likes: {},
        modelId: 0,
        checkLiked: {},
        flag: '',
        numberOfLikes: 0,
        numberOfComments: 0,
        showMore: true
    },
    computed: {
        ...mapState('like', [
            'like',
            'commentTotal',
            'checkLike'
        ]),
        ...mapState('auth', {
            user: state => state.user
        })
    },
    methods: {
        ...mapActions('like', [
            'likeActivity'
        ]),

        showMembersLiked() {
            this.flagShowListMember = true
        }
    },
    components: {
        ListMemberLiked
    }
}
</script>

<style lang="scss" scoped>
    .liked {
        background: #ff5e3a !important;
    }
    .mrg-top {
        margin-top: 0px !important;
    }
    .fill {
        fill: #ff5e3a;
    }
    .info-like {
        margin-right: 5px;
        .post-add-icon.inline-items {
            margin-right: 0px;
        }
    }
    .span-show-name {
        position: relative;
        :after {
            content: '';
            position: absolute;
            border-style: solid;
            border-width: 0 6px 6px 0;
            border-color: transparent #3f4257;
            display: block;
            width: 0;
            z-index: 1;
            bottom: -6px;
            left: 28px;
        }
        .show-name {
            bottom: 10px;
            padding: 10px;
            display: none;
            position: absolute;
            background: #3f4257;
            margin-bottom: 13px;
            border-radius: 5px;
            li {
                padding: initial;
                border-bottom: initial;
                background-color: initial;
            }
        }
        &:hover {
            cursor: pointer;
            .show-name {
                display: block;
            }
        }
    }

    pre {
        font-family: Roboto, -apple-system, system-ui, BlinkMacSystemFont,
            "Segoe UI", "Helvetica Neue", Arial, sans-serif !important;
        font-size: 0.812rem !important;
        color: #edf2f6;
        margin-bottom: 0px;
    }

    .more-like:hover {
        color: #ff5e3a;
    }
</style>
