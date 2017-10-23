<template lang="html">
    <div class="post-additional-info inline-items" v-if="showMore">
        <icon-like
            :likes="likes"
            :checkLiked="checkLiked"
            :flag="flag"
            :modelId="modelId"
            :numberOfComments="numberOfComments"
            :numberOfLikes="numberOfLikes"
            :modelClosed="modelClosed"
            :permission="permission"
            :roomLike="roomLike">
        </icon-like>

        <ul class="friends-harmonic" v-if="like[flag][modelId].numberOfLikes">
            <li v-for="(item, index) in like[flag][modelId]" v-if="index < 3">
                <router-link
                    :to="{ name: 'user.timeline', params: { slug: item.user.slug }}"
                    class="h6 post__author-name fn">
                    <img :src="item.user.image_thumbnail" :alt="item.user.name">
                </router-link>
            </li>

            <li v-if="like[flag][modelId].numberOfLikes > 3">
                <a href="javascript:void(0)"
                    @click="showMembersLiked()"
                    class="all-users">+{{ like[flag][modelId].numberOfLikes - 3 }}</a>
            </li>
        </ul>

        <div class="like-empty" v-if="like[flag][modelId].numberOfLikes == 0">
            {{ $t('post.like.like_first') }}
        </div>
        <div class="names-people-likes" v-else>
            <router-link :to="{ name: 'user.timeline', params: { slug: like[flag][modelId][index].user.slug }}"
                v-for="(item, index) in like[flag][modelId]"
                :key="item.id"
                v-if="index < 2">
                {{ item.user.name }}<span class="temp-name" v-if="like[flag][modelId].numberOfLikes > 2 && index == 0">,</span>
                <span class="temp-name" v-if="like[flag][modelId].numberOfLikes == 2 && index == 0">
                    {{ $t('post.like.and') }}
                </span>
            </router-link>
            <span v-if="like[flag][modelId].numberOfLikes < 3">
                {{ $t('post.like.liked_this') }}
            </span>
            <span v-else="like[flag][modelId].numberOfLikes">
                {{ $t('post.like.and') }}</br>
                <span @click="showMembersLiked()" class="more-like">
                    {{ (like[flag][modelId].numberOfLikes - 2) + ' ' + $t('post.like.more_like') }}
                </span>
            </span>
        </div>

        <div :class="{
            'mrg-top-zero': (like[flag][modelId].numberOfLikes == 0),
            'mrg-top': (like[flag][modelId].numberOfLikes == 1 || like[flag][modelId].numberOfLikes == 2),
            'comments-shared': true
        }">
            <a href="javascript:void(0)" class="post-add-icon inline-items">
                <svg class="olymp-speech-balloon-icon">
                    <use xlink:href="/frontend/icons/icons.svg#olymp-speech-balloon-icon"></use>
                </svg>
                <span>{{ commentTotal[flag][modelId] }}</span>
            </a>
        </div>

        <list-member-liked
            :modelId="modelId"
            :flag="flag"
            :flagShowListMember.sync="flagShowListMember">
        </list-member-liked>
    </div>

    <icon-like
        v-else
        :likes="likes"
        :checkLiked="checkLiked"
        :flag="flag"
        :modelId="modelId"
        :numberOfComments="numberOfComments"
        :numberOfLikes="numberOfLikes"
        :modelClosed="modelClosed"
        :permission="permission"
        :roomLike="roomLike">
    </icon-like>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import ListMemberLiked from './ListMemberLiked.vue'
import IconLike from './IconLike.vue'
import noty from '../../helpers/noty'

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
        showMore: true,
        modelClosed: '',
        permission: '',
        roomLike: ''
    },
    computed: {
        ...mapState('like', [
            'like',
            'commentTotal',
            'checkLike',
        ]),
        ...mapState('auth', {
            user: state => state.user
        })
    },
    methods: {
        ...mapActions('like', [
            'getListMemberLiked'
        ]),

        showMembersLiked() {
            this.getListMemberLiked({
                modelId: this.modelId,
                model: this.flag,
                lastPage: 1,
                pageCurrent: 0
            })
            .then(status => {
                this.flagShowListMember = true
            })
            .catch(err => {
                const message = this.$i18n.t('messages.join_campaign_fail')
                noty({ text: message, force: true, container: false })
            })
        }
    },
    components: {
        ListMemberLiked,
        IconLike
    }
}
</script>

<style lang="scss" scoped>
    .liked {
        background: #ff5e3a !important;
    }

    .fill {
        fill: #ff5e3a;
    }

    .more-like{
        &:hover {
            color: #767a91;
            text-decoration: underline;
            cursor: pointer;
        }
    }

    .friends-harmonic {
        li {
            height: 26px;
            img {
                width: 26px;
                height: 26px;
            }
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
                border: 0px;
                &::before {
                    border: 0px;
                    left: -22px;
                }
            }
        }
        &:hover {
            cursor: pointer;
            .show-name {
                display: block;
            }
        }
    }

    .post-additional-info {
        margin-top: 20px;
        padding: 18px 10px 15px 10px;
        border-bottom: 1px solid #e6ecf5;
        .mrg-top-zero {
            margin-top: 0px !important;
        }
        .mrg-top {
            margin-top: 3px !important;
        }
    }

    .temp-name {
        font-weight: initial;
    }

    .like-empty {
        width: 63%;
    }

    .names-people-likes {
        width: 46%;
        margin-right: 0px;
    }
</style>
