<template lang="html">
    <div>
        <like
            v-if="type == 'like'"
            :likes="likes"
            :checkLiked="checkLiked"
            :flag="flag"
            :modelId="modelId"
            :numberOfComments="numberOfComments"
            :numberOfLikes="numberOfLikes"
            :showMore="showMore">
        </like>

        <like-infor
            v-if="type == 'like-infor'"
            :flag="flag"
            :modelId="modelId"
            :numberOfLikes="numberOfLikes">
        </like-infor>

    </div>
</template>

<script>
    import Like from './Like.vue'
    import LikeInfor from './LikeInfor.vue'
    import { mapActions, mapState } from 'vuex'

    export default {
        created() {
            this.setLike({
                likes: this.likes,
                checkLike: this.checkLiked,
                modelId: this.modelId,
                flag: this.flag,
                numberOfLikes: this.numberOfLikes,
                numberOfComments: this.numberOfComments,
            })
        },
        props: {
            likes: {},
            modelId: 0,
            checkLiked: {},
            flag: '',
            type: '',
            numberOfLikes: 0,
            numberOfComments: 0,
            showMore: true
        },
        methods: {
            ...mapActions('like', [
                'setLike'
            ]),
        },
        components: {
           Like,
           LikeInfor
        }
    }
</script>

<style lang="scss" scoped>
</style>
