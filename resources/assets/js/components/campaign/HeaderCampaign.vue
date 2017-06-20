<template lang="html">
    <div>
        <div class="header-spacer"></div>

        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="ui-block">
                        <div class="top-header top-header-favorit">
                            <div class="top-header-thumb">
                                <img src="/images/top-header2.jpg" alt="nature">
                                <div class="top-header-author">
                                    <div class="author-thumb">
                                        <img src="/images/author-main2.jpg" alt="author">
                                    </div>
                                    <div class="author-content">
                                        <a href="#" class="h3 author-name">{{ campaign.hashtag}}</a>
                                        <div class="country">{{ campaign.title}}</div>
                                    </div>
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
                                        <svg class="olymp-chat---messages-icon"><use xlink:href="icons/icons.svg#olymp-chat---messages-icon"></use></svg>
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
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
     computed: {
        ...mapGetters('campaign',
            ['campaign'])
    }
}
</script>

<style lang="scss">
</style>
