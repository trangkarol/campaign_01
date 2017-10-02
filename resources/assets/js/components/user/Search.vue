<template>
    <div>
        <div class="container">
            <div>
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="mrg-top ui-block">
                            <div class="ui-block-title">
                                <h6 class="title" v-if="!totalUser">
                                    {{ $tc('user.search.result_finded', 0) + ' "' + keyword + '"' }}
                                </h6>
                                <h6 class="title" v-else-if="totalUser == 1">
                                    {{ $tc('user.search.result_finded', 1) + ' "' + keyword + '"' }}
                                </h6>
                                <h6 class="title" v-else>
                                    {{ $tc('user.search.result_finded', 2, { value: totalUser }) + ' "' + keyword + '"' }}
                                </h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-12 col-sm-12 col-xs-12" v-if="totalUser" v-for="(result, index) in usersFinded">
                        <div class="ui-block">
                            <div class="birthday-item inline-items">
                                <div class="author-thumb">
                                    <img :src="result.image_small" alt="author">
                                </div>
                                <div class="birthday-author-name">
                                    <router-link class="h6 author-name" :to="{ name: 'user.timeline', params: { slug: result.slug }}">
                                        {{ result.name }}
                                    </router-link>
                                    <div class="birthday-date">{{ result.email }}</div>
                                </div>
                                <request-friend :friend="result" :classTemp="'search'"></request-friend>
                            </div>
                        </div>
                    </div>
                    <div class="load-more" v-if="showLoadUser">
                        <i v-if="loadingUser" class="fa fa-spinner fa-pulse fa-3x fa-fw" aria-hidden="true"></i>
                        <a href="javascript:void(0)" @click="loadUser">
                            {{ $t('user.search.view_more') }}
                        </a>
                    </div>
                </div>
            </div>
            <div>
                <div>
                    <div class="ui-block">
                        <table class="forums-table">
                            <thead v-if="!totalCampaign">
                                <tr>
                                    <th class="forum" colspan="4">
                                        <h6 class="result-campaign-h6 title">
                                            {{ $tc('user.search.result_campaign_finded', 0) + ' "' + keyword + '"' }}
                                        </h6>
                                    </th>
                                </tr>
                            </thead>
                            <thead v-else>
                                <tr>
                                    <th class="forum">
                                        <span v-if="totalCampaign == 1">
                                            {{ $tc('user.search.result_campaign_finded', 1) + ' "' + keyword + '"' }}
                                        </span>
                                        <span v-else>
                                            {{ $tc('user.search.result_campaign_finded', 2, { value: totalCampaign }) + ' "' + keyword + '"' }}
                                        </span>
                                    </th>
                                    <th class="topics">
                                        {{ $t('user.search.number_of_participants') }}
                                    </th>
                                    <th class="posts">
                                        {{ $t('user.search.number_of_events') }}
                                    </th>
                                    <th class="freshness">
                                        {{ $t('user.search.owner') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="totalCampaign" v-for="result in campaignsFinded">
                                    <td class="forum">
                                        <div class="forum-item">
                                            <img :src="result.media[0].image_medium" alt="forum">
                                            <div class="content">
                                                <router-link class="h6 notification-friend" :to="{ name: 'campaign.timeline', params: { slug: result.slug }}">
                                                    {{ result.title }}
                                                </router-link>
                                                <p class="hashtag">@{{ result.hashtag }}</p>
                                                <span class="chat-message-item">
                                                    <span v-for="tag in result.tags">
                                                        <span class="tag-info">{{ tag.name }}</span>
                                                    </span>
                                                </span>
                                                <p class="check-join" v-if="result.is_owner.length">
                                                    <i class="fa fa-minus-square-o" aria-hidden="true"></i>
                                                    {{ $tc('user.search.check_joined', 0) }}
                                                </p>
                                                <p class="check-join" v-else-if="result.is_member.length">
                                                    <i class="fa fa-check" aria-hidden="true"></i>
                                                    {{ $tc('user.search.check_joined', 1) }}
                                                </p>
                                                <p class="check-join" v-else>
                                                    <i class="fa fa-user-circle" aria-hidden="true"></i>
                                                    {{ $tc('user.search.check_joined', 2) }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="topics">
                                        <span class="h6 count">{{ result.members.length }}</span>
                                    </td>
                                    <td class="posts">
                                        <span class="h6 count">{{ result.events.length }}</span>
                                    </td>
                                    <td class="freshness">
                                        <div class="author-freshness" v-if="result.owner.length">
                                            <div class="author-thumb">
                                                <img :src="result.owner[0].image_thumbnail" alt="author">
                                            </div>
                                            <router-link class="h6 title" :to="{ name: 'user.timeline', params: { slug: result.owner[0].slug }}">
                                                {{ result.owner[0].name }}
                                            </router-link>
                                            <time class="entry-date updated">{{ result.owner[0].email }}</time>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="showLoadCampaign">
                                    <td colspan="4">
                                        <div class="load-more">
                                            <i v-if="loadingCampaign" class="fa fa-spinner fa-pulse fa-3x fa-fw" aria-hidden="true"></i>
                                            <a href="javascript:void(0)" @click="loadCampaign">
                                                {{ $t('user.search.view_more') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Noty from 'noty'
    import noty from '../../helpers/noty'
    import { get } from '../../helpers/api'
    import RequestFriend from './RequestFriend.vue'

    export default {
        metaInfo() {
            return {
                title: this.$t('messages.search')
            }
        },
        data: () => ({
            loadingUser: false,
            loadingCampaign: false,
            usersFinded: [],
            campaignsFinded: [],
            totalPageUser: 1,
            totalPageCampaign: 1,
            pageUser: 1,
            pageCampaign: 1,
            showLoadUser: false,
            showLoadCampaign: false,
            totalUser: 0,
            totalCampaign: 0
        }),
        computed: {
            keyword() {
                return this.$route.params.keyword
            }
        },
        created() {
            this.getResult()
        },
        watch: {
            // call again the method if the route changes
            $route () {
                this.getResult()
            }
        },
        methods: {
            getResult() {
                // 1 is page
                // 6 is the amount of data retrieved
                // all is type which gets all data
                get(`search/1/6/all?keyword=${this.$route.params.keyword}`)
                    .then(res => {
                        this.showLoadUser = false
                        this.showLoadCampaign = false
                        this.usersFinded = res.data.users
                        this.campaignsFinded = res.data.campaigns
                        this.totalPageUser = Math.ceil(res.data.totalUser / 6)
                        this.totalPageCampaign = Math.ceil(res.data.totalCampaign / 6)
                        this.totalUser = res.data.totalUser
                        this.totalCampaign = res.data.totalCampaign

                        if (this.totalPageUser > this.pageUser) {
                            this.showLoadUser = true
                        }

                        if (this.totalPageCampaign > this.pageCampaign) {
                            this.showLoadCampaign = true
                        }
                    })
                    .catch(err => {
                        noty({
                            text: this.$i18n.t('messages.connection_error'),
                            container: false,
                            force: true
                        })
                    })
            },
            loadUser() {
                if (this.pageUser < this.totalPageUser) {
                    this.loadingUser = true
                    get(`search/${++this.pageUser}/6/user?keyword=${this.$route.params.keyword}`)
                        .then(res => {
                            this.usersFinded = this.usersFinded.concat(Object.keys(res.data.users).map(
                                function (key) {
                                    return res.data.users[key];
                                })
                            )

                            if (this.totalPageUser == this.pageUser) {
                                this.showLoadUser = false
                            }

                            this.loadingUser = false
                        })
                        .catch(err => {
                            noty({
                                text: this.$i18n.t('messages.connection_error'),
                                container: false,
                                force: true
                            })
                        })
                }
            },
            loadCampaign() {
                if (this.pageCampaign < this.totalPageCampaign) {
                    this.loadingCampaign = true
                    get(`search/${++this.pageCampaign}/6/campaign?keyword=${this.$route.params.keyword}`)
                        .then(res => {
                            this.campaignsFinded = this.campaignsFinded.concat(Object.keys(res.data.campaigns).map(
                                function (key) {
                                    return res.data.campaigns[key];
                                })
                            )

                            if (this.totalPageCampaign == this.pageCampaign) {
                                this.showLoadCampaign = false
                            }

                            this.loadingCampaign = false
                        })
                        .catch(err => {
                            noty({
                                text: this.$i18n.t('messages.connection_error'),
                                container: false,
                                force: true
                            })
                        })
                }
            }
        },
        components: {
            RequestFriend
        }
    }

</script>

<style lang="scss" scoped>
.check-join {
    margin: 4px 0px 0px;
    .fa {
        margin-left: 1px;
    }
}

.author-thumb {
    img {
        width: 40px;
        height: 40px;
    }
 }

.fa-spinner {
    font-size: 20px;
    margin-top: 1px;
    margin-right: 10px;
}

.hashtag {
    margin: 4px 0px;
}

.more {
    color: white;
}

.result-campaign-h6 {
    color: white;
    margin-bottom: 0px;
}

.mrg-top {
    margin-top: 20px;
}

.birthday-author-name {
    width: 50%;
}

.btn-choose {
    background: #38a9ff;
    margin-bottom: 0;
    float: right;
    padding: 0.5rem 1.5em;
    font-size: 11px;
    font-weight: bold;
    border-radius: 0.2rem;
    margin-top: 5px;
}

.cover-info {
    margin: 13px 0px;
    padding: 10px 0px;
    border: 1px solid #d9d9d9;
    .author-thumb {
        img {
            width: 50px !important;
            height: 50px !important;
        }
    }
    .h5 {
        font-size: 15px;
    }
    .col-md-5 {
        text-align: right;
        padding-top: 7%;
    }
}

.load-more {
    width: 100%;
    text-align: right;
    margin: auto 15px 10px;
    border-radius: 2px;
    padding: 15px;
    a {
        font-size: 15px;
        border: 1px solid #ffffff;
        padding: 6px 20px;
        background: #ffffff;
        border-radius: 4px;
        margin-right: 1%;
        color: #38a9ff;
        box-shadow: 0 1px 1px 0 rgba(0,0,0,.14), 0 2px 1px -1px rgba(0,0,0,.12), 0 1px 3px 0 rgba(0,0,0,.2);
        &:hover {
            text-decoration: underline;
            cursor: pointer;
            background: #79c2fa;
            color: white;
            border: 1px solid #79c2fa;
        }
    }
}

.btn-friend {
    padding: 5px 7px;
    border: 0px;
    color: white;
    border: 1px solid white;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.14), 0 2px 1px -1px rgba(0,0,0,.12), 0 1px 3px 0 rgba(0,0,0,.2);
    font-weight: bolder;
    background: #38a9ff;
}

.forum-item img {
    float: left;
    margin-right: 15px;
    width: 90px;
    height: 90px;
}
</style>
