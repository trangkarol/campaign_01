<template>
    <div>
        <user-header :user="authUser" v-if="authUser.id == currentPageUser.id"></user-header>
        <user-header :user="currentPageUser" v-else></user-header>
        <router-view></router-view>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import { EventBus } from '../../EventBus.js'
    import slug from '../../helpers/mixin/getFullSlug'
    import UserHeader from './Header.vue'

    export default {
        mixins: [slug],
        metaInfo() {
            return {
                title: this.currentPageUser.name || this.$t('homepage.loading')
            }
        },
        data: () => ({
            pageType: 'user',
        }),
        computed: {
            ...mapState('user',[
                'currentPageUser'
            ]),
            ...mapState('auth', {
                authUser: 'user'
            })
        },
        created() {
            EventBus.$emit('redirect-page')
            this.$socket.emit('viewing_like', `user${this.pageId}`)
            this.getUser(this.pageId)
        },
        watch: {
            // call again the method if the route changes
            $route () {
                this.getUser(this.pageId)
                EventBus.$emit('redirect-page')
            }
        },
        methods: {
            ...mapActions('user', ['getUser'])
        },
        components: {
            UserHeader
        },
        beforeDestroy() {
            this.$socket.emit('stop_view_like', `user${this.pageId}`)
        }
    }
</script>
