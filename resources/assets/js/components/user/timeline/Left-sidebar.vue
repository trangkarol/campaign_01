<template>
    <div class="col-xl-3 pull-xl-6 col-lg-6 pull-lg-0 col-md-6 col-sm-12 col-xs-12">
        <div class="left-home-box">
            <div class="ui-block">
                <div class="ui-block-title">
                    <h6 class="title">{{ $t('user.sidebar.profile_intro') }}</h6>
                </div>
                <div class="ui-block-content">
                    <ul class="widget w-personal-info item-block">
                        <li>
                            <span class="title">{{ $t('user.sidebar.about_me') }}:</span>
                            <span class="text">{{ currentPageUser.about }}</span>
                        </li>
                        <li>
                            <span class="title">{{ $t('user.sidebar.birthday') }}:</span>
                            <span class="text">{{ currentPageUser.birthday }}</span>
                        </li>
                        <li>
                            <span class="title">{{ $t('user.sidebar.address') }}:</span>
                            <span class="text">{{ currentPageUser.address }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="ui-block" style="display: none;">
                <div class="ui-block-title">
                    <h6 class="title">James’s Badges</h6>
                </div>
                <div class="ui-block-content">

                    <ul class="widget w-badges">
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge1.png" alt="author">
                                <div class="label-avatar bg-primary">2</div>
                            </a>
                        </li>
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge4.png" alt="author">
                            </a>
                        </li>
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge3.png" alt="author">
                                <div class="label-avatar bg-blue">4</div>
                            </a>
                        </li>
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge6.png" alt="author">
                            </a>
                        </li>
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge11.png" alt="author">
                            </a>
                        </li>
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge8.png" alt="author">
                            </a>
                        </li>
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge10.png" alt="author">
                            </a>
                        </li>
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge13.png" alt="author">
                                <div class="label-avatar bg-breez">2</div>
                            </a>
                        </li>
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge7.png" alt="author">
                            </a>
                        </li>
                        <li>
                            <a href="24-CommunityBadges.html">
                                <img src="/images/badge12.png" alt="author">
                            </a>
                        </li>
                    </ul>

                </div>
            </div>

            <div class="ui-block" style="display: none;">
                <div class="ui-block-title">
                    <h6 class="title">My Spotify Playlist</h6>
                </div>

                <ol class="widget w-playlist">
                    <li class="js-open-popup" data-popup-target=".playlist-popup">
                        <div class="playlist-thumb">
                            <img src="/images/playlist6.jpg" alt="thumb-composition">
                            <div class="overlay"></div>
                            <a href="#" class="play-icon">
                                <svg class="olymp-music-play-icon-big"><use xlink:href="icons/icons-music.svg#olymp-music-play-icon-big"></use></svg>
                            </a>
                        </div>

                        <div class="composition">
                            <a href="#" class="composition-name">The Past Starts Slow...</a>
                            <a href="#" class="composition-author">System of a Revenge</a>
                        </div>

                        <div class="composition-time">
                            <time class="published" datetime="2017-03-24T18:18">3:22</time>
                            <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                <ul class="more-dropdown">
                                    <li>
                                        <a href="#">Add Song to Player</a>
                                    </li>
                                    <li>
                                        <a href="#">Add Playlist to Player</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </li>

                    <li class="js-open-popup" data-popup-target=".playlist-popup">
                        <div class="playlist-thumb">
                            <img src="/images/playlist7.jpg" alt="thumb-composition">
                            <div class="overlay"></div>
                            <a href="#" class="play-icon">
                                <svg class="olymp-music-play-icon-big"><use xlink:href="icons/icons-music.svg#olymp-music-play-icon-big"></use></svg>
                            </a>
                        </div>

                        <div class="composition">
                            <a href="#" class="composition-name">The Pretender</a>
                            <a href="#" class="composition-author">Kung Fighters</a>
                        </div>

                        <div class="composition-time">
                            <time class="published" datetime="2017-03-24T18:18">5:48</time>
                            <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                <ul class="more-dropdown">
                                    <li>
                                        <a href="#">Add Song to Player</a>
                                    </li>
                                    <li>
                                        <a href="#">Add Playlist to Player</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </li>
                    <li class="js-open-popup" data-popup-target=".playlist-popup">
                        <div class="playlist-thumb">
                            <img src="/images/playlist8.jpg" alt="thumb-composition">
                            <div class="overlay"></div>
                            <a href="#" class="play-icon">
                                <svg class="olymp-music-play-icon-big"><use xlink:href="icons/icons-music.svg#olymp-music-play-icon-big"></use></svg>
                            </a>
                        </div>

                        <div class="composition">
                            <a href="#" class="composition-name">Blood Brothers</a>
                            <a href="#" class="composition-author">Iron Maid</a>
                        </div>

                        <div class="composition-time">
                            <time class="published" datetime="2017-03-24T18:18">3:06</time>
                            <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                <ul class="more-dropdown">
                                    <li>
                                        <a href="#">Add Song to Player</a>
                                    </li>
                                    <li>
                                        <a href="#">Add Playlist to Player</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </li>
                    <li class="js-open-popup" data-popup-target=".playlist-popup">
                        <div class="playlist-thumb">
                            <img src="/images/playlist9.jpg" alt="thumb-composition">
                            <div class="overlay"></div>
                            <a href="#" class="play-icon">
                                <svg class="olymp-music-play-icon-big"><use xlink:href="icons/icons-music.svg#olymp-music-play-icon-big"></use></svg>
                            </a>
                        </div>

                        <div class="composition">
                            <a href="#" class="composition-name">Seven Nation Army</a>
                            <a href="#" class="composition-author">The Black Stripes</a>
                        </div>

                        <div class="composition-time">
                            <time class="published" datetime="2017-03-24T18:18">6:17</time>
                            <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                <ul class="more-dropdown">
                                    <li>
                                        <a href="#">Add Song to Player</a>
                                    </li>
                                    <li>
                                        <a href="#">Add Playlist to Player</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </li>
                    <li class="js-open-popup" data-popup-target=".playlist-popup">
                        <div class="playlist-thumb">
                            <img src="/images/playlist10.jpg" alt="thumb-composition">
                            <div class="overlay"></div>
                            <a href="#" class="play-icon">
                                <svg class="olymp-music-play-icon-big"><use xlink:href="icons/icons-music.svg#olymp-music-play-icon-big"></use></svg>
                            </a>
                        </div>

                        <div class="composition">
                            <a href="#" class="composition-name">Killer Queen</a>
                            <a href="#" class="composition-author">Archiduke</a>
                        </div>

                        <div class="composition-time">
                            <time class="published" datetime="2017-03-24T18:18">5:40</time>
                            <div class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                <ul class="more-dropdown">
                                    <li>
                                        <a href="#">Add Song to Player</a>
                                    </li>
                                    <li>
                                        <a href="#">Add Playlist to Player</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </li>
                </ol>
            </div>

            <div class="ui-block" style="display: none;">
                <div class="ui-block-title">
                    <h6 class="title">Twitter Feed</h6>
                </div>

                <ul class="widget w-twitter">
                    <li class="twitter-item">
                        <div class="author-folder">
                            <img src="/images/twitter-avatar1.png" alt="avatar">
                            <div class="author">
                                <a href="#" class="author-name">Space Cowboy</a>
                                <a href="#" class="group">@james_spiegelOK</a>
                            </div>
                        </div>
                        <p>Tomorrow with the agency we will run 5 km for charity. Come and give us your support! <a href="#" class="link-post">#Daydream5K</a></p>
                        <span class="post__date">
                            <time class="published" datetime="2017-03-24T18:18">
                                2 hours ago
                            </time>
                        </span>
                    </li>
                    <li class="twitter-item">
                        <div class="author-folder">
                            <img src="/images/twitter-avatar1.png" alt="avatar">
                            <div class="author">
                                <a href="#" class="author-name">Space Cowboy</a>
                                <a href="#" class="group">@james_spiegelOK</a>
                            </div>
                        </div>
                        <p>Check out the new website of “The Bebop Bar”! <a href="#" class="link-post">bytle/thbp53f</a></p>
                        <span class="post__date">
                            <time class="published" datetime="2017-03-24T18:18">
                                16 hours ago
                            </time>
                        </span>
                    </li>
                    <li class="twitter-item">
                        <div class="author-folder">
                            <img src="/images/twitter-avatar1.png" alt="avatar">
                            <div class="author">
                                <a href="#" class="author-name">Space Cowboy</a>
                                <a href="#" class="group">@james_spiegelOK</a>
                            </div>
                        </div>
                        <p>The Sunday is the annual agency camping trip and I still haven’t got a tent  <a href="#" class="link-post">#TheWild #Indoors</a></p>
                        <span class="post__date">
                            <time class="published" datetime="2017-03-24T18:18">
                                Yesterday
                            </time>
                        </span>
                    </li>
                </ul>
            </div>

            <div class="ui-block" style="display: none;">
                <div class="ui-block-title">
                    <h6 class="title">Last Videos</h6>
                </div>
                <div class="ui-block-content">
                    <ul class="widget w-last-video">
                        <li>
                            <a href="https://vimeo.com/ondemand/viewfromabluemoon4k/147865858" class="play-video play-video--small">
                                <svg class="olymp-play-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-play-icon"></use></svg>
                            </a>
                            <img src="/images/video8.jpg" alt="video">
                            <div class="video-content">
                                <div class="title">System of a Revenge - Hypnotize...</div>
                                <time class="published" datetime="2017-03-24T18:18">3:25</time>
                            </div>
                            <div class="overlay"></div>
                        </li>
                        <li>
                            <a href="https://youtube.com/watch?v=excVFQ2TWig" class="play-video play-video--small">
                                <svg class="olymp-play-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-play-icon"></use></svg>
                            </a>
                            <img src="/images/video7.jpg" alt="video">
                            <div class="video-content">
                                <div class="title">Green Goo - Live at Dan’s Arena</div>
                                <time class="published" datetime="2017-03-24T18:18">5:48</time>
                            </div>
                            <div class="overlay"></div>
                        </li>
                    </ul>
                </div>
            </div>
            <campaign-involve></campaign-involve>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions, mapGetters } from 'vuex'
    import ShowText from '../../libs/ShowText.vue'
    import CampaignInvolve from '../../home/CampaignInvolve.vue'

    export default {
        computed: {
            ...mapState('user', ['currentPageUser']),
        },
        components: {
            ShowText,
            CampaignInvolve
        },
    }
</script>

<style lang="scss">

</style>
