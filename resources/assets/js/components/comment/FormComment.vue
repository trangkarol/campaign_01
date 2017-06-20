<template lang="html">
    <form class="comment-form inline-items form-comment" @submit.prevent="addComment">
        <div class="post__author author vcard inline-items">
            <img :src="user.url_file" :alt="user.name">
        </div>
        <div class="form-group with-icon-right">
            <textarea class="form-control" :placeholder="$t('campaigns.write-comment')" v-model="comment.content" @keydown="addComment"></textarea>
            <div class="add-options-message">
            </div>
        </div>

    </form>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { get, post } from '../../helpers/api'
import axios from 'axios'

export default {
	data: () => ({
       comment: { content: '' }
	}),
    props: ['modelId'],
    computed: {
        // ...mapState('comment', ['modelId']),
		...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        }),
	},
    methods: {
        ...mapActions('comment', ['commentDetail']),
        addComment(e) {
            console.log(this.modelId)
            if (e.keyCode === 13) {
                 post('comment/create-comment-event/' + this.modelId, this.comment)
                .then(res => {
                    if (res.data.http_status.status) {
                        this.commentDetail(this.modelId)
                        //set content of form input comment is empty
                        this.comment.content = '';
                    }
                })
                .catch(err => {
                    // this.spinner = false
                    // const message = this.$i18n.t('messages.regiser_fail')
                    // noty({ text: message, force: true})
                })
            }
        }
    },
	components: {

	}
}
</script>

<style lang="scss">
</style>
