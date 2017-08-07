<template lang="html">
    <div>
        <header-campaign></header-campaign>
        <div class="container">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    import HeaderCampaign from './HeaderCampaign.vue'
    import { mapActions } from 'vuex'
    export default {
        created: function () {
            this.campaignDetail(this.$route.params.id)
        },
        methods: {
            ...mapActions('campaign', [
                'campaignDetail',
            ])
        },
        components: {
           HeaderCampaign
        }
    }
</script>

<style lang="scss">
</style>
