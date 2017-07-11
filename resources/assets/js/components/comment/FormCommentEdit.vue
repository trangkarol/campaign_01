<template lang="html">
    <form class="comment-form inline-items">
        <div class="form-group with-icon-right">
            <textarea class="form-control" v-model="comment.content" @keydown="editComments"></textarea>
        </div>
        <a href="javascript:void(0)" @click="cancelEdit" >{{ $t('form.cancel') }}</a>
    </form>
</template>

<script>
import { mapActions } from 'vuex'
import axios from 'axios'

export default {
    data: () => ({
        comment: { content: '' }
    }),
    props: ['parentComment', 'flagEdit'],
    created() {
        this.changeContent()
    },
    computed: {
        //
    },
    methods: {
        ...mapActions('comment', ['editComment']),
        editComments(e) {
            if (e.keyCode === 13) {
                let data = {
                    comment: this.comment,
                    commentId: this.parentComment.id,
                    modelId: this.parentComment.commentable_id
                }

                this.editComment(data)
                 .then(status => {
                        if (status) {
                            this.$emit('changeFlagEdit')
                        }
                    })
            }
        },
        changeContent() {
            this.comment.content = this.parentComment.content
        },
        cancelEdit() {
            this.$emit('changeFlagEdit')
        }
    },
    components: {
        //
    }
}
</script>

<style lang="scss">
</style>
