<template lang="html">
    <div class="col-xl-6 push-xl-3 col-lg-12 push-lg-0 col-sm-12 col-xs-12">
		<div ref="scrollContainer" id ="data-loadmore">
			<list-events></list-events>
		</div>
		<a ref="loadmore" href="javascript:void(0)" class="btn btn-control btn-more" data-container="newsfeed-items-grid">
			<i  v-show="loading" class="fa fa-spinner fa-spin"></i>
			<div class="ripple-container"></div>
		</a>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { get } from '../../helpers/api'
import axios from 'axios'
import ListEvents from './ListEvents.vue'

export default {
	computed: {
		...mapState('campaign',[
			'campaign',
			'events',
			'loading',
		]),
	},
	// mounted() {
	// 	 $(window).scroll(() => {
	// 			this.loading = $(window).scrollTop() > $(window).height()
	// 			if (this.loading) {
	// 				this.loadMore()
	// 			}
	// 		})
	// 	},
	methods: {
		...mapActions('campaign', ['fetchData']),
			// loadMore() {
			// 	var data = { campaignId: this.$route.params.id, events: this.events }
			// 	this.fetchData(data)
			// }
	},
	components: {
		ListEvents
	}
}
</script>

<style lang="scss">
</style>
