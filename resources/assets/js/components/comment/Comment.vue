<template lang="html">
    <div>
        <list-comment
            :model-id="modelId"
            :flag="flag"
            :classListComment="classListComment"
            :deleteDate="deleteDate">
        </list-comment>
        <form-comment
            :model-id="modelId"
            :comment-parent-id="0"
            :flag="flag"
            :classFormComment="classFormComment"
            v-if="deleteDate == null">
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
    props: {
        comments: {},
        modelId: 0,
        flag: '',
        classListComment: '',
        classFormComment: '',
        numberOfComments: 0,
        deleteDate: null,
    },
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
