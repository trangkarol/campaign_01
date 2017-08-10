<template lang="html">
    <div v-if="comments[flag][modelId] != null " :class="classListComment">
        <a ref="loadmore"
            href="javascript:void(0)"
            class="btn btn-control btn-more"
            data-container="newsfeed-items-grid"
            v-show="loading == modelId">
            <i class="fa fa-spinner fa-spin"></i>
            <div class="ripple-container"></div>
        </a>
        <a href="javascript:void(0)" class="more-comments"
            v-show="comments[flag][modelId].length <= paginates[flag][modelId].total"
            @click="handelLoadMoreParentComment({
                modelId: modelId,
                flag: flag,
                pageCurrent: paginates[flag][modelId].current_page,
                lastPage: paginates[flag][modelId].last_page
            })">
             {{ $t('campaigns.more-comment') }}
             <span>+</span>
        </a>
        <ul class="comments-list">
            <li v-for="(comment, index) in comments[flag][modelId]" class="has-children comment">
                <div class="post__author author vcard inline-items" v-if="comment.user != null">
                    <router-link
                        :to="{ name: 'user.timeline', params: { id: comment.user.id }}"
                        class="h6 post__author-name fn">
                        <img :src="comment.user.image_thumbnail" :alt="comment.user.name">
                    </router-link>

                    <div class="author-date">
                        <router-link
                            :to="{ name: 'user.timeline', params: { id: comment.user.id }}"
                            class="h6 post__author-name fn">
                            {{ comment.user.name }}
                        </router-link>
                        <div class="post__date">
                            <timeago
                                :max-time="86400 * 365"
                                class="published date-format"
                                :since="comment.created_at">
                            </timeago>
                        </div>
                    </div>
                    <div class="more" v-if="comment.user.id == user.id">
                        <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                        <ul class="more-dropdown" >
                            <li>
                                <a href="javascript:void(0)"
                                    @click="editComments(comment, index)">
                                    {{ $t('form.edit') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"
                                    @click="deleteComment({
                                        commentId: comment.id,
                                        modelId: modelId,
                                        flag: flag,
                                        commentParentId: 0 })">
                                    {{ $t('form.delete') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="show-text">
                    <show-text
                        :text="comment.content"
                        :show_char=100
                        :show="$t('events.show_more')"
                        :hide="$t('events.show_less')"
                        :number_char_show=70
                        v-if="flagEdit != comment.id">
                    </show-text>
                </div>

                <form-comment-edit
                    :parentComment="comment"
                    :flagEdit="flagEdit"
                    :flag="flag"
                    :classFormComment="''"
                    @changeFlagEdit="changeFlagEdit"
                    v-if="flagEdit == comment.id">
                </form-comment-edit>

                <master-like
                    :likes="comment.likes"
                    :flag="'comment'"
                    :checkLiked="comment.checkLike"
                    :type="'like'"
                    :modelId="comment.id"
                    :numberOfLikes="comment.number_of_likes"
                    :showMore="false">
                </master-like>

                <a href="javascript:void(0)"
                    @click="showSubComment(comment, index)"
                    class="reply">{{ $t('campaigns.reply') }}
                </a>
                <a href="javascript:void(0)"
                    @click="showSubComment(comment, index)"
                    class="reply">
                    <span>
                        {{ comment.number_of_comments }}
                    </span>
                </a>
                <a href="javascript:void(0)"
                    @click="hideSubComment()"
                    class="reply"
                    v-if="flagReply == comment.id">
                    {{ $t('form.hidden') }}
                </a>
                <ul class="children" v-if ="comment.sub_comment != null" >
                    <li v-show="loading == comment.id">
                        <a ref="loadmore" href="javascript:void(0)"
                            class="btn btn-control btn-more" data-container="newsfeed-items-grid" >
                            <i class="fa fa-spinner fa-spin"></i>
                            <div class="ripple-container"></div>
                        </a>
                    </li>
                    <li class="view-more"
                        v-if="comment.sub_comment.data.length <=  comment.number_of_comments && flagReply == comment.id">
                        <a href="javascript:void(0)" class="more-comments"
                            @click="handelLoadMoreSubComment({
                                commentParentId: comment.id,
                                modelId: modelId,
                                flag: flag,
                                pageCurrent: comment.sub_comment.current_page,
                                lastPage: comment.sub_comment.last_page
                            })">
                            {{ $t('campaigns.more-comment') }}
                            <span>+</span>
                        </a>
                    </li>
                    <li v-for="subComment in comment.sub_comment.data" v-if="flagReply == comment.id">
                        <div class="post__author author vcard inline-items">

                            <router-link :to="{ name: 'user.timeline', params: { id: subComment.user.id }}"
                                class="h6 post__author-name fn">
                                <img :src="subComment.user.image_thumbnail" :alt="subComment.user.name">
                            </router-link>

                            <div class="author-date">
                                <router-link :to="{ name: 'user.timeline', params: { id: subComment.user.id }}"
                                    class="h6 post__author-name fn">
                                    {{ subComment.user.name }}
                                </router-link>
                                <div class="post__date">
                                    <timeago
                                        :max-time="86400 * 365"
                                        class="published date-format"
                                        :since="subComment.created_at">
                                    </timeago>
                                </div>
                            </div>
                            <div class="more" v-if="subComment.user.id == user.id">
                                <svg class="olymp-three-dots-icon">
                                    <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                </svg>
                                <ul class="more-dropdown" >
                                    <li>
                                        <a href="javascript:void(0)"
                                            @click="editComments(subComment, index)">{{ $t('form.edit') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)"
                                            @click="deleteComment({
                                                commentId: subComment.id,
                                                modelId: modelId,
                                                flag: flag,
                                                commentParentId: subComment.parent_id })">
                                            {{ $t('form.delete') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="show-text">
                            <show-text
                                :text="subComment.content"
                                :show_char=100
                                :show="$t('events.show_more')"
                                :hide="$t('events.show_less')"
                                :number_char_show=70
                                v-if="flagEdit != subComment.id">
                            </show-text>
                        </div>

                        <form-comment-edit
                            :parentComment="subComment"
                            v-if="flagEdit == subComment.id"
                            :flagEdit="flagEdit"
                            :flag="flag"
                            :classFormComment="''"
                            @changeFlagEdit="changeFlagEdit">
                        </form-comment-edit>

                        <master-like
                            :likes="subComment.likes"
                            :flag="'comment'"
                            :type="'like'"
                            :checkLiked="subComment.checkLike"
                            :modelId="subComment.id"
                            :numberOfLikes="subComment.number_of_likes"
                            :showMore="false">
                        </master-like>
                    </li>
                </ul>
                <form-comment
                    :model-id="modelId"
                    :comment-parent-id="comment.id"
                    :flag="flag"
                    :classFormComment="''"
                    v-if="flagReply == comment.id">
                </form-comment>
            </li>
        </ul>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import FormComment from './FormComment.vue'
import FormCommentEdit from './FormCommentEdit.vue'
import ShowText from '../libs/ShowText.vue'
import MasterLike from '../like/MasterLike.vue'

export default {
    data: () => ({
        flagEdit: '',
        flagReply: '',
        loading: '',
        flagMore: true
    }),
    props: {
        modelId: 0,
        flag: '',
        classListComment: '',
        numberOfComments: 0
    },
    computed: {
        ...mapState('comment', [
            'comments',
            'paginates'
        ]),
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        })
    },
    methods: {
        ...mapActions('comment', [
            'commentDetail',
            'editComment',
            'deleteComment',
            'loadMoreParentComment',
            'loadMoreSubComment'
        ]),
        showSubComment(comment, index) {
            this.flagReply = comment.id
        },
        editComments(comment, index) {
            this.flagEdit = comment.id
        },
        changeFlagEdit() {
            this.flagEdit = ''
        },
        hideSubComment() {
            this.flagReply = ''
        },
        handelLoadMoreParentComment(data) {
            this.$Progress.start()
            this.loading = data.modelId

            this.loadMoreParentComment(data)
                .then(res => {
                    this.$Progress.finish()
                    this.loading = ''
                })
                .catch(err => {
                    this.$Progress.fail()
                    this.loading = ''
                })
        },
        handelLoadMoreSubComment(data) {
            this.$Progress.start()
            this.loading = data.commentParentId

            this.loadMoreSubComment(data)
                .then(res => {
                    this.$Progress.finish()
                    this.loading = ''
                })
                .catch(err => {
                    this.$Progress.fail()
                    this.loading = ''
                })
        },
        convertToHTML(text) {
            return text.replace(/(?:\r\n|\r|\n)/g, '<br />');
        }
    },
    components: {
        FormComment,
        FormCommentEdit,
        ShowText,
        MasterLike
    }
}
</script>

<style lang="scss" scoped>
    .comments-list {
        li {
            border-bottom: 0px;
            padding: 15px 20px 0px 20px;
        }

        .children {
            margin: 12px -20px 0px -20px;
            li {
                border-bottom: 1px solid #e6ecf5;
                padding-bottom: 10px;
                &:last-child {
                    border-bottom: 0px;
                }
            }
        }
        .view-more {
            padding: 0px !important;
            &:before {
                background-color: initial;
                border: 0px;
            }
        }
    }

    .more {
        margin-right: 0px;
         > .more-dropdown {
            top: 75%;
            right: -20px;
            width: 110px;
            padding: 2px 15px;
             > li {
                padding: initial;
                border-bottom: initial;
                background-color: initial;
                position: initial;
                border-left: 0px;
                &:before {
                    background-color: initial;
                    border: 0px;
                }
                i {
                    margin-right: 3px;
                }
            }
        }
    }

    .has-children {
        .post__author {
            margin-bottom: 5px;
             .more {
                float: right;
                font-size: 16px;
                margin-right: 0px;
            }
        }
    }

    .btn-control.btn-more {
        fill: #fff;
        background: initial;
        margin: 20px auto;
        line-height: initial;
        margin: 20px auto -25px;

        > i {
            font-size: 30px;
            color: #b6b6b6;
        }
    }

    .show-text {
        margin-bottom: 5px;
        margin-left: 40px;
    }

    .comment-form {
        background: white;
        border-bottom: 1px solid #e6ecf5;
    }

    .date-format {
        font-size: 13px;
        color: #bbb;
    }

</style>
