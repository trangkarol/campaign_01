<template lang="html">
    	<div class="col-xl-3 pull-xl-6 col-lg-6 pull-lg-0 col-md-6 col-sm-12 col-xs-12">
			<div class="ui-block">
				<div class="ui-block-title">
					<h6 class="title">{{ $t('campaigns.campaign-infor') }}</h6>
					<a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
				</div>
				<div class="ui-block-content">
					<ul class="widget w-personal-info item-block">
						<li>
							<span class="text">{{ campaign.description }}</span>
						</li>

						<li v-if="campaign.start_day !== null">
							<span class="title">{{ $t('campaigns.start-day') }}:</span>
							<span class="text">{{ campaign.start_day }}</span>
						</li>

						<li v-if="campaign.end_day !== null">
							<span class="title">{{ $t('campaigns.end-day') }}:</span>
							<span class="text">{{ campaign.end_day }}</span>
						</li>

						<li v-if="campaign.timeout_campaign !== null">
							<span class="title">{{ $t('campaigns.operation-time') }}:</span>
							<span class="text">{{ campaign.timeout_campaign }}</span>
						</li>

						<li>
							<span class="title">{{ $t('campaigns.address') }}:</span>
							<span class="text">{{ campaign.address }}</span>
						</li>
						<li v-if="campaign.owner != null">
							<span class="title">{{ $t('campaigns.email') }}:</span>
							<a href="#" class="text">{{ campaign.owner.email }}</a>
						</li>
						<li v-if="campaign.owner != null">
							<span class="title">{{ $t('campaigns.phone') }}:</span>
							<a href="#" class="text">{{ campaign.owner.phone }}</a>
						</li>
					</ul>

					<div class="widget w-socials">
						<h6 class="title">{{ $t('campaigns.other-social') }}:</h6>
						<a href="#" class="social-item bg-facebook">
							<i class="fa fa-facebook" aria-hidden="true"></i>
							Facebook
						</a>
						<a href="#" class="social-item bg-twitter">
							<i class="fa fa-twitter" aria-hidden="true"></i>
							Twitter
						</a>
						<a href="#" class="social-item bg-green">
							<i class="fa fa-dribbble" aria-hidden="true"></i>
							Spotify
						</a>
					</div>
				</div>
			</div>

			<div class="ui-block">
				<div class="ui-block-title">
					<h6 class="title">{{ $t('campaigns.location') }}</h6>
					<a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
				</div>

				<div class="widget w-contacts">
					<!-- Google map -->
					<div class="section">
						<div id="map">
							<gmap-map
								:center="positions"
								:zoom="6"
								map-type-id="terrain"
								style="width: 100%; height: 200px"
								>
								<gmap-marker
								:position="positions"
								:clickable="true"
								:draggable="true"
								:title="campaign.address"
								></gmap-marker>
							</gmap-map>
						</div>
						<ul></ul>
					</div>
					<!-- End Google map -->
				</div>
			</div>

		</div>
</template>

<script>
	import { mapState, mapActions, mapGetters } from 'vuex'

    export default {
		computed: {
			...mapGetters('campaign',
				['campaign']),

		    positions() {
				return { lat: parseFloat(this.campaign.latitude), lng: parseFloat(this.campaign.longitude) }
			},
		}
    }
</script>

<style lang="scss">
</style>
