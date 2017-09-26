<template lang="html">
    <span>
        <a href="javascript:void(0)"
            class="post-add-icon inline-items"
            @click="likeActivities">
            <svg class="olymp-heart-icon">
                <use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"
                    :class="{ fill: checkLike[flag][modelId] }">
                </use>
            </svg>
        </a>
        <span class="span-show-name" @click="showMembersLiked()">
            <ul class="show-name" v-if="like[flag][modelId].numberOfLikes">
                <li v-for="(item, index) in like[flag][modelId]" v-if="index < 4">
                    <pre>{{ item.user.name }}</pre>
                </li>
                <li v-if="like[flag][modelId].numberOfLikes >= 4">
                    <pre>{{ $t('post.like.and') + (like[flag][modelId].numberOfLikes - 4) + $t('post.like.more_like') }}</pre>
                </li>
            </ul>
            {{ like[flag][modelId].numberOfLikes }}
        </span>

        <list-member-liked
            :modelId="modelId"
            :flag="flag"
            :flagShowListMember.sync="flagShowListMember">
        </list-member-liked>
    </span>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import noty from '../../helpers/noty'
import ListMemberLiked from './ListMemberLiked.vue'

export default {
    data: () => ({
        flagShowListMember: false
    }),
    props: {
        likes: {},
        modelId: 0,
        checkLiked: {},
        flag: '',
        numberOfLikes: 0,
        numberOfComments: 0,
        modelClosed: '',
        onlyComment: '',
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
            'likeActivity',
            'getListMemberLiked',
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
        },
        likeActivities() {
            this.likeActivity({
                model: this.flag,
                modelId: this.modelId,
                user: this.user,
                flag: this.flag,
                numberOfLikes: this.like[this.flag][this.modelId].numberOfLikes,
                deleteDate: this.like[this.flag][this.modelId].deleteDate,
                messages: this.modelClosed,
                permission: this.permission
            })
            .then(res => {
                let data = {
                    like: res.like,
                    modelId: this.modelId,
                    flag: this.flag,
                    user: this.user,
                    numberOfLikes: res.numberOfLikes,
                    deleteDate: this.deleteDate
                }

                this.$socket.emit('likeActivity', {
                    newLike: data,
                    room: this.roomLike
                })
            })
            .catch(err => {
                if (err.response.data.http_status.code == 401) {
                    noty({
                            text: this.permission,
                            force: true, container: false
                        })
                }
            })
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

    .fill {
        fill: #ff5e3a;
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

    .post-add-icon {
        margin-right: 0px;
        svg {
            margin-right: 5px;
        }
    }

    pre {
        font-family: Roboto, -apple-system, system-ui, BlinkMacSystemFont,
            "Segoe UI", "Helvetica Neue", Arial, sans-serif !important;
        font-size: 0.812rem !important;
        color: #edf2f6;
        margin-bottom: 0px;
    }
</style>
