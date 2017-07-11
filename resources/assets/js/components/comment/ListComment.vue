<template lang="html">
    <div v-if="comments[modelId] != null">
        <ul class="comments-list">
            <li v-for="(comment, index) in comments[modelId]" class="has-children comment">
                <div class="post__author author vcard inline-items" v-if="comment.user != null">
                    <img :src="comment.user.url_file" alt="author">

                    <div class="author-date">
                        <a class="h6 post__author-name fn" href="#"> {{ comment.user.name }}</a>
                        <div class="post__date">
                            <timeago
                                :max-time="86400 * 365"
                                class="published"
                                :since="comment.created_at">
                            </timeago>
                        </div>
                    </div>

                    <div class="more" v-if="comment.user.id == user.id">
                        <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                        <ul class="more-dropdown" >
                            <li>
                                <a href="javascript:void(0)" @click="editComments(comment, index)">{{ $t('form.edit') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" @click="deleteComment({ commentId: comment.id, modelId: modelId })">{{ $t('form.delete') }}</a>
                            </li>
                        </ul>
                    </div>

                </div>

                <p v-if="flagEdit != comment.id">{{ comment.content }}</p>
                <form-comment-edit :parentComment="comment" v-if="flagEdit == comment.id" :flagEdit="flagEdit" @changeFlagEdit="changeFlagEdit"></form-comment-edit>

                <a href="#" class="post-add-icon inline-items">
                    <svg class="olymp-heart-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use></svg>
                    <span v-if="comment.likes != null">{{ comment.likes.length }}</span>
                </a>

                <a href="javascript:void(0)" @click="showSubComment(comment, index)" class="reply">{{ $t('campaigns.reply') }}</a>

                <a href="javascript:void(0)" @click="showSubComment(comment, index)" v-if ="comment.sub_comment != null" class="reply">
                    <span v-if=" comment.sub_comment.length > 0">{{ comment.sub_comment.length }} {{ $t('form.answers') }}</span>
                </a>

                <a href="javascript:void(0)" @click="hideSubComment()" class="reply" v-if="flagReply == comment.id">{{ $t('form.hidden') }}</a>

                <ul class="children" v-if ="comment.sub_comment != null" >
                    <li v-for="subComment in comment.sub_comment" v-if="flagReply == comment.id">
                        <div class="post__author author vcard inline-items">
                            <img :src="subComment.user.url_file" alt="author">

                            <div class="author-date">
                                <a class="h6 post__author-name fn" href="#">{{ subComment.user.name }}</a>
                                <div class="post__date">
                                    <timeago
                                        :max-time="86400 * 365"
                                        class="published"
                                        :since="subComment.created_at">
                                    </timeago>
                                </div>
                            </div>

                            <div class="more" v-if="subComment.user.id == user.id">
                                <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                <ul class="more-dropdown" >
                                    <li>
                                        <a href="javascript:void(0)" @click="editComments(subComment, index)">{{ $t('form.edit') }}</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" @click="deleteComment({ commentId: subComment.id, modelId: modelId })">{{ $t('form.delete') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <p v-if="flagEdit != subComment.id">{{ subComment.content }}</p>
                        <form-comment-edit :parentComment="subComment" v-if="flagEdit == subComment.id" :flagEdit="flagEdit" @changeFlagEdit="changeFlagEdit"></form-comment-edit>

                        <a href="#" class="post-add-icon inline-items">
                            <svg class="olymp-heart-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use></svg>
                            <span v-if="subComment.likes != null">{{ subComment.likes.length }}</span>
                        </a>
                    </li>
                </ul>

                <form-comment :model-id="modelId" :comment-parent-id="comment.id"  :flag="flag" v-if="flagReply == comment.id"></form-comment>
            </li>
        </ul>
        <a href="#" class="more-comments" v-if="comments[modelId].length > 3">{{ $t('campaigns.more-comment') }}<span>+</span></a>

    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import FormComment from './FormComment.vue'
import FormCommentEdit from './FormCommentEdit.vue'

export default {
    data: () => ({
        flagEdit: '',
        flagReply: ''
    }),
    props: ['modelId', 'flag'],
    computed: {
        ...mapState('comment', ['comments']),
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        }),
    },
    methods: {
        ...mapActions('comment', ['commentDetail', 'editComment', 'deleteComment']),
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
        }
    },
    components: {
        FormComment,
        FormCommentEdit
    }
}
</script>

<style lang="scss">
</style>
