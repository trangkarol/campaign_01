<template lang="html">
    <div class="col-xl-3 pull-xl-9 col-lg-3 pull-lg-9 col-md-12 col-sm-12 col-xs-12">
        <div class="ui-block">
            <div class="your-profile">
                <div class="ui-block-title ui-block-title-small">
                    <h6 class="title">{{ $t('campaigns.manager-campaign') }}</h6>
                </div>

                <div class="ui-block-title">
                    <router-link
                        :to="{ name: 'campaign.member_request',
                        params: { id: campaign.id }}"
                        class="h6 title">
                        {{ $t('campaigns.member-request')}}
                    </router-link>
                </div>
                <div class="ui-block-title">
                    <router-link
                        :to="{ name: 'campaign.list_member',
                        params: { id: campaign.id }}"
                        class="h6 title">
                        {{ $t('campaigns.list-members')}}
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapState } from 'vuex'

    export default {
        created: function () {
            //
        },
        computed: {
            ...mapState('campaign', [
                'campaign'
            ])
        },
        methods: {
            //
        },
        components: {
          //
        }
    }
</script>

<style lang="scss">
</style>
