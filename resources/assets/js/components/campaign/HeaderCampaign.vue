<template lang="html">
    <div>
        <div class="header-spacer"></div>

        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="ui-block">
                        <div class="top-header top-header-favorit">
                            <div class="top-header-thumb" v-if="campaign.campaign_images != null">
                                <img :src="campaign.campaign_images.url_file" alt="nature">
                                <div class="top-header-author">
                                    <div class="author-thumb" v-if="campaign.owner != null">
                                        <img :src="campaign.owner.url_file" alt="author">
                                    </div>
                                        <a href="#" class="h3 author-name">{{ campaign.title }}</a>
                                        <div class="country">#{{ campaign.hashtag }}</div>
                                </div>
                            </div>
                            <div class="profile-section">
                                <div class="row">
                                    <div class="col-xl-8 offset-xl-2 col-lg-8 offset-lg-2 col-md-12 offset-md-0">
                                        <ul class="profile-menu">
                                            <li>
                                                <router-link to="/campaign/timeline" class="active">{{ $t('campaigns.timeline') }}</router-link>
                                            </li>
                                            <li>
                                                <a href="13-FavouritePage-About.html">About</a>
                                            </li>
                                            <li>
                                                <a href="07-ProfilePage-Photos.html">Photos</a>
                                            </li>
                                            <li>
                                                <a href="09-ProfilePage-Videos.html">Videos</a>
                                            </li>
                                            <li>
                                                <a href="14-FavouritePage-Statistics.html">Statistics</a>
                                            </li>
                                            <li>
                                                <a href="15-FavouritePage-Events.html">Events</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="control-block-button">
                                    <a href="#" class="btn btn-control bg-primary">
                                        <svg class="olymp-star-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use></svg>
                                    </a>

                                    <a href="#" class="btn btn-control bg-purple">
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
import { mapState } from 'vuex'

export default {
    computed: {
        ...mapState('auth', {
            authenticated: state => state.authenticated,
            user: state => state.user
        }),
        ...mapState('campaign', ['campaign'])
    }
}
</script>

<style lang="scss">
</style>
