<template lang="html">
    <div>
        <list-comment :model-id="modelId" :flag="flag"></list-comment>
        <form-comment :model-id="modelId" :comment-parent-id="0" :flag="flag"></form-comment>
    </div>
</template>

<script>
import { mapActions } from 'vuex'
import ListComment from './ListComment.vue'
import FormComment from './FormComment.vue'

export default {
    created() {
            this.changeComment({ comments: this.comments, modelId: this.modelId })
    },
    props: ['comments', 'modelId', 'flag'],
    computed: {
        //
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
