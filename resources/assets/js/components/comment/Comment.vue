<template lang="html">
    <div>
        <list-comment :model-id="modelId"></list-comment>
        <form-comment :model-id="modelId"></form-comment>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import ListComment from './ListComment.vue'
import FormComment from './FormComment.vue'

export default {
    // props: {
    //     // comments: Array,
    //     modelId: Number
    // },
    props: ['comments', 'modelId'],
    computed: {
        //
	},
    methods: {
        ...mapActions('comment', ['getComment', 'changeComment']),
    },
	components: {
        ListComment,
        FormComment
	},
    created() {
        // this.getComment({ comments: this.comments, modelId: this.modelId })
        this.changeComment(this.comments)
        console.log(this)
    }
}
</script>

<style lang="scss">
</style>
