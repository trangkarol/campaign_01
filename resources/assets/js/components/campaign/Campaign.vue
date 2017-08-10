<template lang="html">
    <div class="campaign-container">
        <header-campaign></header-campaign>
        <div class="container">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    import HeaderCampaign from './HeaderCampaign.vue'
    import { mapActions } from 'vuex'
    import slug from '../../helpers/mixin/getFullSlug'

    export default {
        mixins: [slug],
        data() {
            return {
                pageType: 'campaign'
            }
        },
        created: function () {
            this.campaignDetail(this.pageId)
                .then(status => {
                    //
                })
                .catch( err => {
                    this.$router.push('/')
                })
        },
        methods: {
            ...mapActions('campaign', [
                'campaignDetail',
            ]),
        },
        components: {
           HeaderCampaign
        }
    }
</script>

<style lang="scss" scoped>
    .campaign-container {
        background: #edf2f6;
    }
</style>
