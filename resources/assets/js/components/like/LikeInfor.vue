<template lang="html">
    <a href="javascript:void(0)"
        :class="{ liked: checkLike[flag][modelId], btn: true, 'btn-control': true }"
        @click="likeActivity({
            model: flag,
            modelId: modelId,
            user: user,
            flag: flag,
            numberOfLikes: like[flag][modelId].numberOfLikes
        })">
        <svg class="olymp-like-post-icon">
            <use xlink:href="/frontend/icons/icons.svg#olymp-like-post-icon"></use>
        </svg>
    </a>
</template>

<script>
import { mapActions, mapState } from 'vuex'

export default {
    props: {
        modelId: 0,
        flag: '',
        numberOfLikes: 0
    },
    computed: {
        ...mapState('like', [
            'like',
            'checkLike'
        ]),
        ...mapState('auth', {
            user: state => state.user
        })
    },
    methods: {
        ...mapActions('like', [
            'setLike',
            'likeActivity'
        ]),
    },
}
</script>

<style lang="scss" scoped>
    .liked {
        background: #ff5e3a !important;
    }
</style>
