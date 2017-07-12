<template lang="html">
    <form class="comment-form inline-items" @submit.prevent="addComment">
        <div class="post__author author vcard inline-items">
            <img :src="user.url_file" :alt="user.name">
        </div>
        <div class="form-group with-icon-right">
            <textarea class="form-control" :placeholder="$t('campaigns.write-comment')" v-model="comment.content" @keydown="addComments"></textarea>
        </div>

    </form>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import axios from 'axios'

export default {
    data: () => ({
       comment: { content: '' },
       flagAction: 'create'
    }),
    props: ['modelId', 'commentParentId', 'flag'],
    computed: {
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        }),
    },
    methods: {
        ...mapActions('comment', ['addComment']),
        addComments(e) {
            if (e.keyCode === 13) {
                this.addComment({
                    modelId: this.modelId,
                    commentParentId: this.commentParentId,
                    flag: this.flag,
                    comment: this.comment,
                    flagAction: this.flagAction
                })
                .then(status => {
                    if (status) {
                        this.comment.content = ''
                    }
                })
            }
        }
    },
    components: {
        //
    }
}
</script>

<style lang="scss">
</style>
