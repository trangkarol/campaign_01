<template lang="html">
    <form class="comment-form inline-items" :class="classFormComment">
        <div class="form-group with-icon-right">
            <textarea
                class="form-control"
                v-model="comment.content"
                @keydown="editComments"
                @input="input($event)">
            </textarea>
        </div>
        <a href="javascript:void(0)" @click="cancelEdit" >{{ $t('form.cancel') }}</a>
    </form>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import axios from 'axios'
import noty from '../../helpers/noty'

export default {
    data: () => ({
        comment: { content: '' },
        flagAction: 'edit'
    }),
    props: [
        'parentComment',
        'flagEdit',
        'flag',
        'classFormComment'
    ],
    created() {
        this.changeContent()
    },
    computed: {
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        }),
    },
    methods: {
        ...mapActions('comment', [
            'editComment'
        ]),
        editComments: _.debounce(function (e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                if (this.comment.content.trim() != '') {
                    let data = {
                        comment: this.comment,
                        commentId: this.parentComment.id,
                        commentParentId: this.parentComment.parent_id,
                        flag: this.flag,
                        modelId: this.parentComment.commentable_id,
                        flagAction: this.flagAction
                    }
                    console.log('data', data)
                    this.editComment(data)
                    .then(status => {
                        status && this.$emit('changeFlagEdit')
                    })
                    .catch(err => {
                        //
                    })
                } else {
                    const message = this.$i18n.t('messages.comment_not_empty')
                    noty({ text: message, force: true, container: false })
                }
            }
        }, 100),
        changeContent() {
            this.comment.content = this.parentComment.content
        },
        cancelEdit() {
            this.$emit('changeFlagEdit')
        },
        input(e) {
            e.target.style.height = "5px";
            e.target.style.height = (e.target.scrollHeight) + "px";
        }
    },
    components: {
        //
    }
}
</script>

<style lang="scss" scoped>
.comment-form textarea {
    resize: none;
    overflow: hidden;
    min-height: 45px;
    height: 45px;
    padding: 12px;

    &:focus {
        min-height: 70px;
    }
}
</style>
