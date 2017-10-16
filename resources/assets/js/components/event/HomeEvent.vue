<template>
    <div v-if="loading">
        <show-event></show-event>
        <search-action></search-action>
        <router-view></router-view>
    </div>
</template>

<script>
    import ShowEvent from './ShowEvent.vue'
    import SearchAction from './SearchAction.vue'
    import ListAction from './ListAction.vue'
    import { mapState, mapActions, mapMutations } from 'vuex'
    import slug from '../../helpers/mixin/getFullSlug'
    export default {
        mixins: [slug],
        metaInfo() {
            return {
                title: this.event.title || this.$t('homepage.loading')
            }
        },
        data() {
            return  {
                pageType: 'event'
            }
        },
        computed: {
            ...mapState([
                'loading'
            ]),
            ...mapState('event', [ 'event' ])
        },

        created() {
            const eventId = this.pageId
            this.$socket.emit('viewing_event', `event${eventId}`)
            this.pageType = null
            const campaignId = this.pageId
            this.$Progress.start()
            this.get_event({
                eventId: eventId,
                campaignId: campaignId
            })
            .then(sucess => {
                this.$Progress.finish()
            })
            .catch(err => {
                this.$Progress.fail()
                if (err.response.data.http_status.code == 404 || err.response.data.http_status.code == 401) {
                    this.$router.replace('/not-found')
                }
            })
        },

        methods: {
            ...mapActions('event', [
                'get_event'
            ]),
            ...mapMutations('event', {
                change_status: 'CHANGE_DONATION_STATUS'
            })
        },

        components: {
            ShowEvent,
            SearchAction
        },

        sockets: {
            accept_donation(data) {
                const goal = this.event.complete_percent.filter(goal => goal.id == data.goal_id)[0]
                let updatedData = goal.donations.filter(donate => donate.id == data.donate_id)[0]
                updatedData.status = data.status
                this.change_status(updatedData)
            }
        },

        beforeDestroy() {
            this.$socket.emit('stop_view_even', `event${this.event.id}`)
        }
    }
</script>
<style type="scss">

</style>
