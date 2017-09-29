<template lang="html">
    <div>
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="ui-block">
                        <div class="top-header top-header-favorit">
                            <div class="top-header-thumb" v-if="campaign.campaign_images != null">
                                <img :src="campaign.campaign_images.image_default" :alt="campaign.name" class="images-campaign">
                                <div class="top-header-author">
                                    <div class="avatar-campaign author-thumb"
                                        :style="{ 'background-image': 'url(' + campaign.campaign_images.image_medium + ')' }"
                                        v-if="campaign.owner != null">
                                    </div>
                                </div>
                                <div class="title-campaign">
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
                                                    :to="{ name: 'campaign.timeline' }"
                                                    :class="checkActiveUrl('campaign.timeline')">
                                                    {{ $t('campaigns.timeline') }}
                                                </router-link>
                                            </li>
                                            <li>
                                                <router-link
                                                    :to="{ name: 'campaign.about' }"
                                                    :class="checkActiveUrl('campaign.about')">
                                                    {{ $t('campaigns.about') }}
                                                </router-link>
                                            </li>
                                            <li>
                                                <router-link
                                                    :to="{ name: 'campaign.photo' }"
                                                    :class="checkActiveUrl('campaign.photo')">
                                                    {{ $t('campaigns.photos') }}
                                                </router-link>
                                            </li>
                                            <li>
                                                <router-link
                                                    :to="{ name: 'campaign.campaign_related' }"
                                                    :class="checkActiveUrl('campaign.campaign_related')">
                                                    {{ $t('campaigns.campaign-related') }}
                                                </router-link>
                                            </li>
                                            <li>
                                                <router-link
                                                    :to="{ name: 'campaign.statistic', params: { slug: campaign.slug }}"
                                                    :class="checkActiveUrl('campaign.statistic')">
                                                    {{ $t('campaigns.statistic.statistic') }}
                                                </router-link>
                                            </li>
                                            <li v-if="checkPermission || checkAdmin">
                                                <router-link
                                                    :to="{ name: 'campaign.list_member' }"
                                                    :class="checkActiveUrl('campaign.list_member')
                                                        || checkActiveUrl('campaign.member_request')
                                                        || checkActiveUrl('campaign.update')">
                                                    {{ $t('campaigns.campaign-management') }}
                                                </router-link>
                                            </li>
                                            <li>
                                                <router-link
                                                    :to="{ name: 'campaign.events_closed' }"
                                                    :class="checkActiveUrl('campaign.events_closed')">
                                                    {{ $t('campaigns.events_closed') }}
                                                </router-link>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="control-block-button">
                                    <a href="javascript:void(0)" class="btn btn-control bg-primary">
                                        <svg class="olymp-star-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use>
                                        </svg>
                                    </a>

                                    <a href="javascript:void(0)" class="btn btn-control bg-purple">
                                        <svg class="olymp-chat---messages-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-chat---messages-icon"></use>
                                        </svg>
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
import { EventBus } from '../../EventBus.js'

export default {
    metaInfo() {
        return {
            title: this.campaign.title || this.$t('homepage.loading')
        }
    },
    computed: {
        ...mapGetters('campaign', [
            'checkPermission',
        ]),
        ...mapState('auth', [
            'user',
            'checkAdmin'
        ]),
        ...mapState('campaign', ['campaign'])
    },
    created() {
        EventBus.$emit('redirect-page')
    },
    watch: {
        $route () {
            EventBus.$emit('redirect-page')
        }
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

<style lang="scss" scoped>
    .images-campaign {
        max-height: 500px !important;
        object-fit: cover;
    }

    .profile-section {
        padding: 55px 0px 20px;
    }

    .title-campaign {
        position: absolute;
        left: 250px;
        bottom: -35px;
        z-index: 1;
        .country {
            margin-top: 15px;
            color: #494c62;
            font-size: 20px;
        }
    }

    .avatar-campaign {
        top: 150px !important;
        background-position: 50% 25%;
        background-size: cover;
        background-repeat: no-repeat;
    }
</style>
