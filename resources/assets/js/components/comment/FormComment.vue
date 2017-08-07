<template lang="html">
    <form class="comment-form inline-items" @submit.stop.prevent="addComment" :class="classFormComment">
        <div class="post__author author vcard inline-items">
            <router-link :to="{ name: 'user.timeline', params: { id: user.id }}">
                <img :src="user.image_thumbnail" :alt="user.name">
            </router-link>
        </div>
        <div class="form-group with-icon-right">
            <textarea
                class="form-control"
                name="content"
                :placeholder="$t('campaigns.write-comment')"
                v-model="comment.content"
                v-validate="'required'"
                @input="input($event)"
                @keydown="addComments">
            </textarea>
        </div>

    </form>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import axios from 'axios'
import noty from '../../helpers/noty'

export default {
    data: () => ({
       comment: { content: '' },
       flagAction: 'create'
    }),
    props: [
        'modelId',
        'commentParentId',
        'flag',
        'classFormComment'
    ],
    computed: {
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        }),
        ...mapState('comment', ['paginates']),
    },
    methods: {
        ...mapActions('comment', ['addComment']),
        addComments: _.debounce(function (e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                if (this.comment.content.trim() != '') {
                    this.addComment({
                        modelId: this.modelId,
                        commentParentId: this.commentParentId,
                        flag: this.flag,
                        comment: this.comment,
                        flagAction: this.flagAction,
                        commentTotal: this.paginates[this.modelId].total
                    })
                    .then(status => {
                        this.comment.content = ''
                        e.target.style.height = "5px"
                    })
                    .catch(err => {
                        //
                    })
                } else {
                    this.comment.content = ''
                    e.target.style.height = "5px"
                    const message = this.$i18n.t('messages.comment_not_empty')
                    noty({ text: message, force: true, container: false })
                }
            }
        }, 100),
        input(e) {
            e.target.style.height = "5px";
            e.target.style.height = (e.target.scrollHeight)+"px";
        }
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
