\<template lang="html">
    <a href="javascript:void(0)"
        :class="{ liked: checkLike[flag][modelId], btn: true, 'btn-control': true }"
        @click="likeActivities()">
        <svg class="olymp-like-post-icon">
            <use xlink:href="/frontend/icons/icons.svg#olymp-like-post-icon"></use>
        </svg>
    </a>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import noty from '../../helpers/noty'

export default {
    props: {
        modelId: 0,
        flag: '',
        numberOfLikes: 0,
        modelClosed: '',
        permission: '',
        roomLike: ''
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
        likeActivities() {
            this.likeActivity({
                model: this.flag,
                modelId: this.modelId,
                user: this.user,
                flag: this.flag,
                numberOfLikes: this.like[this.flag][this.modelId].numberOfLikes,
                deleteDate: this.like[this.flag][this.modelId].deleteDate,
                messages: this.modelClosed,
                permission: this.permission
            })
            .then(res => {
                 let data = {
                    like: res.like,
                    modelId: this.modelId,
                    flag: this.flag,
                    user: this.user,
                    numberOfLikes: res.numberOfLikes,
                    deleteDate: this.deleteDate
                }

                this.$socket.emit('likeActivity', {
                    newLike: data,
                    room: this.roomLike
                })
            })
            .catch(err => {
                if (err.response.data.http_status.code == 401) {
                    noty({
                            text: this.permission,
                            force: true, container: false
                        })
                }
            })
        }
    },
}
</script>

<style lang="scss" scoped>
    .liked {
        background: #ff5e3a !important;
    }
</style>
