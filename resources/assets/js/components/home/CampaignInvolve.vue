<template>
    <div class="ui-block" v-if="listCampaign.length">
        <div class="ui-block-title">
            <h6 class="title">{{ $t('homepage.campaigns_may_join') }}</h6>
        </div>
        <ol class="widget w-playlist">
            <li class="js-open-popup" v-for="campaign in listCampaign">
                <div class="playlist-thumb">
                    <img :src="campaign.media[0].image_medium" alt="author">
                    <div class="overlay"></div>
                    <a href="javascript:void(0)" class="play-icon">
                        <svg class="olymp-music-play-icon-big">
                            <use xlink:href="icons/icons-music.svg#olymp-music-play-icon-big"></use>
                        </svg>
                    </a>
                </div>
                <div class="composition">
                    <router-link class="composition-name" :to="{ name: 'campaign.timeline', params: { slug: campaign.slug }}">
                        <span v-if="campaign.title.length < 51">{{ campaign.title }}</span>
                        <span v-else>{{ campaign.title.substr(0, 50) }}...</span>
                    </router-link>
                    <a href="javascript:void(0)" class="composition-author">
                        Tags:
                        <span v-for="(tag, index) in campaign.tags">
                            {{ tag.name }}<span v-if="index < campaign.tags.length - 1">, </span>
                        </span>
                    </a>
                </div>
                <div class="composition-time">
                    <svg class="olymp-star-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use>
                    </svg>
                </div>
            </li>
        </ol>
    </div>
</template>

<script>
    import noty from '../../helpers/noty'
    import { get } from '../../helpers/api'

    export default {
        data: () => ({
            listCampaign: []
        }),
        created() {
            this.getCampaignInvolve()
        },
        methods: {
            getCampaignInvolve() {
                get('campaign/involve')
                    .then(res => {
                        this.listCampaign = res.data.campaignInvolve
                    })
                    .catch(err => {
                        noty({
                            text: this.$i18n.t('messages.connection_error'),
                            container: false,
                            force: true
                        })
                    })
            },
        }
    }
</script>

<style lang="scss" scoped>
    .js-open-popup {
        img {
            width: 40px;
            height: 40px;
        }
        .composition {
            width: 60%;
        }

        .composition-time {
            padding-top: 4px;
            padding-right: 3px;
            [class^="olymp-"] {
                height: 17px;
                width: 20px;
                fill: #9a9fbe;
                display: inline-block;
            }
        }
    }
</style>
