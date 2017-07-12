<template lang="html">
   <div class="post-additional-info inline-items">

        <a href="javascript:void(0)" class="post-add-icon inline-items">
            <svg class="olymp-heart-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use></svg>
            <span>{{ lengthLike }}</span>
        </a>

        <ul class="friends-harmonic" v-if="likes != null">
            <li v-for="(like, index) in likes" v-if="index <= 10">
                <a href="#">
                    <img :src="like.user.url_file" alt="friend">
                </a>
            </li>
        </ul>

        <div class="names-people-likes">
            <a href="#">You</a>, <a href="#">Elaine</a> and
            <br> 34 more liked this
        </div>

        <div class="comments-shared">
            <a href="javascript:void(0)" class="post-add-icon inline-items">
                <svg class="olymp-speech-balloon-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-speech-balloon-icon"></use></svg>
                <span >{{ lengthComment }}</span>
            </a>
        </div>

    </div>

</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import ListComment from './ListComment.vue'
import FormComment from './FormComment.vue'

export default {
    props: ['likes', 'lik', 'comments', 'flag'],
    computed: {
        lengthLike() {
            return this.likes.length
        },
        lengthComment() {
            return this.comments.length
        }

    },
    methods: {
        ...mapActions('comment', ['getComment', 'changeComment']),
    },
    components: {
        ListComment,
        FormComment
    }
}
</script>

<style lang="scss">
</style>
