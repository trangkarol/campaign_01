<template lang="html">
    <div>
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="ui-block">
                        <div class="top-header top-header-favorit">
                            <div class="top-header-thumb" v-if="campaign.campaign_images != null">
                                <img :src="campaign.campaign_images.image_small" alt="nature" style=" height: 542px;">
                                <div class="top-header-author">
                                    <div class="author-thumb" v-if="campaign.owner != null" style="top: 163px;">
                                        <img :src="campaign.owner.image_thumbnail" alt="author">
                                    </div>
                                        <a href="javascript:void(0)" class="h3 author-name">{{ campaign.title }}</a>
                                        <div class="country">#{{ campaign.hashtag }}</div>
                                </div>
                            </div>
                            <div class="profile-section">
                                <div class="row">
                                    <div class="col-xl-8 offset-xl-2 col-lg-8 offset-lg-2 col-md-12 offset-md-0">
                                        <ul class="profile-menu">
                                            <li>
                                                <router-link
                                                    :to="{ name: 'campaign.timeline',
                                                    params: { id: campaign.id }}"
                                                    :class="checkActiveUrl('campaign.timeline')">
                                                    {{ $t('campaigns.timeline') }}
                                                </router-link>
                                            </li>
                                            <li>
                                                <router-link
                                                    :to="{ name: 'campaign.about',
                                                    params: { id: campaign.id }}"
                                                    :class="checkActiveUrl('campaign.about')">
                                                    {{ $t('campaigns.about') }}
                                                </router-link>
                                            </li>
                                            <li>
                                                <router-link
                                                    :to="{ name: 'campaign.photo',
                                                    params: { id: campaign.id }}"
                                                    :class="checkActiveUrl('campaign.photo')">
                                                    {{ $t('campaigns.photos') }}
                                                </router-link>
                                            </li>
                                            <li>
                                                <router-link
                                                    :to="{ name: 'campaign.campaign_related',
                                                    params: { id: campaign.id }}"
                                                    :class="checkActiveUrl('campaign.campaign_related')">
                                                    {{ $t('campaigns.campaign-related') }}
                                                </router-link>
                                            </li>
                                            <li v-if="checkPermission">
                                                <router-link
                                                    :to="{ name: 'campaign.member_request',
                                                    params: { id: campaign.id }}"
                                                    :class="checkActiveUrl('campaign.member_request')">
                                                    {{ $t('campaigns.manager-campaign') }}
                                                </router-link>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="control-block-button">
                                    <a href="javascript:void(0)" class="btn btn-control bg-primary">
                                        <svg class="olymp-star-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use></svg>
                                    </a>

                                    <a href="javascript:void(0)" class="btn btn-control bg-purple">
                                        <svg class="olymp-chat---messages-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-chat---messages-icon"></use></svg>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
    computed: {
        ...mapGetters('campaign', [
            'checkPermission',
        ]),
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        }),
        ...mapState('campaign', ['campaign'])
    },
    methods : {
        checkActiveUrl(name) {
            if (this.$route.name == name) {
                return 'active'
            }

            return ''
        }
    }
}
</script>

<style lang="css" scoped>
</style>
