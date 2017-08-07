<template lang="html">
    <div class="row">
        <router-view></router-view>
        <left-owner-campaign></left-owner-campaign>
    </div>
</template>

<script>
    import LeftOwnerCampaign from './LeftOwnerCampaign.vue'
    import { mapActions } from 'vuex'

    export default {
        created: function () {
            //
        },
        methods: {
            // ...mapActions('campaign', ['campaignDetail'])
        },
        components: {
           LeftOwnerCampaign
        }
    }
</script>

<style lang="scss">
</style>
