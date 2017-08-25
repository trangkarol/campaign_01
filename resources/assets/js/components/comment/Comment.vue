<template lang="html">
    <div>
        <list-comment
            :model-id="modelId"
            :flag="flag"
            :classListComment="classListComment">
        </list-comment>
        <form-comment
            :model-id="modelId"
            :comment-parent-id="0"
            :flag="flag"
            :classFormComment="classFormComment">
        </form-comment>
    </div>
</template>

<script>
import { mapActions } from 'vuex'
import ListComment from './ListComment.vue'
import FormComment from './FormComment.vue'

export default {
    created() {
        this.changeComment({
            comments: this.comments,
            modelId: this.modelId,
            flag: this.flag,
            numberOfComments: this.numberOfComments,
        })
    },
    props: [
        'comments',
        'modelId',
        'flag',
        'numberOfComments',
        'classListComment',
        'classFormComment'
    ],
    computed: {
        //
    },
    methods: {
        ...mapActions('comment', [
            'getComment',
            'changeComment'
        ]),
    },
    components: {
        ListComment,
        FormComment
    }
}
</script>

<style lang="scss">
</style>
