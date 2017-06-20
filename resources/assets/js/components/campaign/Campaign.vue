<template lang="html">
    <div>
         <header-campaign></header-campaign>
        <div class="container">
	        <div class="row">
                <router-view></router-view>
                <left-campaign ></left-campaign>
                <right-campaign></right-campaign>
            </div>
        </div>
    </div>
</template>

<script>
import HeaderCampaign from './HeaderCampaign.vue'
import LeftCampaign from './LeftCampaign.vue'
import RightCampaign from './RightCampaign.vue'
import { mapState, mapActions, mapGetters } from 'vuex'
import { get } from '../../helpers/api'

export default {
    created: function () {
        this.campaignDetail(this.$route.params.id)
    },
    methods: {
        ...mapActions('campaign',
            ['campaignDetail'])
    },
    components: {
       HeaderCampaign,
       LeftCampaign,
       RightCampaign
    }
}
</script>

<style lang="scss">
</style>
