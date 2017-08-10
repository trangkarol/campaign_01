<template lang="html">
    <div class="row">
        <div class="col-xl-6 push-xl-3 col-lg-12 push-lg-0 col-sm-12 col-xs-12" v-if="events != null">
            <div ref="scrollContainer" id ="data-loadmore">
                <list-events></list-events>
            </div>

            <a ref="loadmore" href="javascript:void(0)" class="btn btn-control btn-more" data-container="newsfeed-items-grid" v-if="events.total > 0">
                <i  v-show="loading" class="fa fa-spinner fa-spin"></i>
                <div class="ripple-container"></div>
            </a>
        </div>

        <left-campaign ></left-campaign>
        <right-campaign></right-campaign>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import ListEvents from './ListEvents.vue'
import LeftCampaign from './LeftCampaign.vue'
import RightCampaign from './RightCampaign.vue'

export default {
    computed: {
        ...mapState('campaign', [
            'campaign',
            'events',
            'loading',
        ]),
    },
    mounted() {
        $(window).scroll(() => {
            if ($(document).height() <= $(window).scrollTop() + $(window).height()) {
                this.loadMore()
            }
        })
    },
    beforeDestroy() {
        $(window).off()
    },
    methods: {
        ...mapActions('campaign', [
            'fetchData'
        ]),
        loadMore() {
            var data = {
                campaignId: this.pageId,
                events: this.events,
                pageNumberEvent: this.events.last_page,
                pageCurrent: this.events.current_page
            }

            this.fetchData(data)
        }
    },

    components: {
        ListEvents,
        LeftCampaign,
        RightCampaign
    }
}
</script>

<style lang="scss">
</style>
