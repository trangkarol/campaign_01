<template lang="html">
    <div>
        <ul class="comments-list">
            <li v-for="(comment, index) in comments[modelId]" class="has-children">
                <div class="post__author author vcard inline-items" v-if="comment.user !== null">
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

                    <a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg></a>

                </div>

                <p>{{ comment.content }}</p>

                <a href="#" class="post-add-icon inline-items">
                    <svg class="olymp-heart-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use></svg>
                    <span v-if="comment.like != null">{{ comment.likes.length }}</span>
                </a>

                <a href="#" class="reply">{{ $t('campaigns.reply') }}</a>

                <ul class="children" v-if ="comment.sub_comment != null">
                    <li v-for="subComment in comment.sub_comment">
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

                        </div>

                        <p>{{ subComment.content }}</p>

                        <a href="#" class="post-add-icon inline-items">
                            <svg class="olymp-heart-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use></svg>
                            <span v-if="subComment.likes != null">{{ subComment.likes.length }}</span>
                        </a>
                        <a href="#" class="reply">{{ $t('campaigns.reply') }}</a>
                    </li>
                </ul>
            </li>
        </ul>

        <a href="#" class="more-comments" v-if ="comments.length > 0">{{ $t('campaigns.more-comment') }}<span>+</span></a>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
	data: () => ({
        // comments: <this class="comments"></this>
	}),
    props: ['modelId'],
    computed: {
        ...mapState('comment', ['comments']),
	},
    methods: {
        ...mapActions('comment', ['commentDetail']),
    }
}
</script>

<style lang="scss">
</style>
