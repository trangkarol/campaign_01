<template>
    <div>
        <div class="main-header bg-events">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 offset-lg-2 col-md-8 offset-md-2 col-sm-12 col-xs-12">
                        <div class="main-header-content">
                            <h1>{{ $t('homepage.welcome_web') }}</h1>
                            <p>{{ $t('homepage.description_web') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <img src="/images/account-bottom.png" alt="friends" class="img-bottom">
        </div>
        <div class="hompage container">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 col-xs-12">
                        <div class="ui-block" v-for="campaign in listCampaign">
                            <article class="hentry blog-post blog-post-v3">
                                <div class="post-thumb">
                                    <img :src="campaign.media[0].image_large" alt="photo">
                                    <p class="post-category">
                                        <span class="span-tags"
                                            v-for="tag in campaign.tags"
                                            v-if="campaign.tags">
                                            {{ tag.name }}
                                        </span>
                                    </p>
                                </div>
                                <div class="post-content">
                                    <div class="author-date">
                                        {{ $t('homepage.by') }}
                                        <span class="h6 post__author-name fn">{{ campaign.owner[0].name }}</span>
                                        <div class="post__date">
                                            <time class="published">
                                               - {{ campaign.created_at }}
                                            </time>
                                        </div>
                                    </div>
                                    <span class="h3 post-title">{{ campaign.title }}</span>
                                    <p class="description-campaign" v-html="campaign.description"></p>
                                    <div class="info-like post-additional-info inline-items">
                                        <div class="names-people-likes">
                                            <p class="post-add-icon inline-items">
                                                <svg class="olymp-heart-icon">
                                                    <use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use>
                                                </svg>
                                                <span v-if="!campaign.number_of_likes">{{ $t('post.like.like_first') }}</span>
                                                <span v-else>{{ campaign.number_of_likes + ' ' + $t('homepage.likes') }}</span>
                                            </p>
                                        </div>
                                        <div class="comments-shared">
                                            <p class="post-add-icon inline-items">
                                                <svg class="olymp-happy-faces-icon left-menu-icon">
                                                    <use xlink:href="/frontend/icons/icons.svg#olymp-happy-faces-icon"></use>
                                                </svg>
                                                <span v-if="campaign.active_users_count == 1">
                                                    {{ $t('homepage.participant') }}
                                                </span>
                                                <span v-else>
                                                    {{ $t('homepage.participants', { value: campaign.active_users_count }) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12">
                        <aside>
                            <div class="ui-block">
                                <article class="hentry blog-post blog-post-v3 featured-post-item">
                                    <div class="post-thumb">
                                        <img src="/images/campaign.jpg" alt="photo">
                                    </div>
                                    <div class="post-content">
                                        <div class="information author-date" >
                                            {{ $t('homepage.information') }}
                                        </div>
                                        <p class="info-web">
                                            <i aria-hidden="true" class="fa fa-user-circle-o"></i>
                                                {{ $t('homepage.user_use', { value: totalUser }) }}
                                            </p>
                                        <p class="info-web">
                                            <i aria-hidden="true" class="fa fa-check"></i>
                                            {{ $t('homepage.campaign_created', { value: totalCampiagn }) }}
                                        </p>
                                        <div class="content">
                                            <span>{{ $t('homepage.create_own_campaign') }}</span>
                                            <router-link :to="{ name: 'login' }" class="create-btn btn btn-bg-secondary btn-md">
                                                {{ $t('homepage.register_now') }}
                                                <div class="ripple-container"></div>
                                            </router-link>
                                        </div>
                                    </div>
                                </article>
                            </div>
                            <div class="ui-block">
                                <div class="ui-block-title">
                                    <h6 class="title">{{ $t('homepage.features') }}</h6>
                                </div>
                            </div>
                            <div class="ui-block">
                                <div class="birthday-item inline-items badges">
                                    <div class="author-thumb">
                                        <img src="/images/badge7.png" alt="author">
                                    </div>
                                    <div class="author-thumb">
                                        <img src="/images/badge9.png" alt="author">
                                    </div>
                                    <div class="author-thumb">
                                        <img src="/images/badge11.png" alt="author">
                                    </div>
                                    <div class="author-thumb">
                                        <img src="/images/badge8.png" alt="author">
                                    </div>
                                    <div class="author-thumb">
                                        <img src="/images/badge4.png" alt="author">
                                    </div>
                                    <div class="birthday-author-name">
                                        <p class="h6 author-name">{{ $t('homepage.make_campaign') }}</p>
                                        <div class="birthday-date">
                                            {{ $t('homepage.make_campaign_description') }}
                                       </div>
                                    </div>
                                    <div class="birthday-author-name">
                                        <p class="h6 author-name">{{ $t('homepage.message_master') }}</p>
                                        <div class="birthday-date">
                                            {{ $t('homepage.message_master_description') }}
                                        </div>
                                    </div>
                                    <div class="birthday-author-name">
                                        <p class="h6 author-name">{{ $t('homepage.friendly') }}</p>
                                        <div class="birthday-date">
                                            {{ $t('homepage.friendly_description') }}
                                        </div>
                                    </div>
                                    <div class="birthday-author-name">
                                        <p class="h6 author-name">{{ $t('homepage.clear_statistics') }}</p>
                                        <div class="birthday-date">
                                            {{ $t('homepage.clear_statistics_description') }}
                                        </div>
                                    </div>
                                    <div class="birthday-author-name">
                                        <p class="h6 author-name">{{ $t('homepage.share_images') }}</p>
                                        <div class="birthday-date">
                                            {{ $t('homepage.share_images_description') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import noty from '../../helpers/noty'
    import { get, post } from '../../helpers/api'
    import { mapState } from 'vuex'

    export default {
        data: () => ({
            listCampaign: [],
            totalUser: 0,
            totalCampiagn: 0
        }),
        created() {
            this.getDataHomePage()
        },
        methods: {
            getDataHomePage() {
                get('homepage')
                    .then(res => {
                        this.totalUser = res.data.totalUser
                        this.totalCampiagn = res.data.campaigns.totalCampaign
                        this.listCampaign = res.data.campaigns.data
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
    }
</script>

<style lang="scss" scoped>
    @media (max-width: 560px) {
        .blog-post.post-additional-info.comments-shared {
            float: none;
        }
    }
    .hompage {
        margin-top: 20px;
        .div-more {
            padding: 20px;
            text-align: center;
            i {
                font-size: 25px;
            }
        }
        .post-category {
            padding: 0px;
            .span-tags {
                display: inline-block;
                background: #00b7ff;
                padding: 5px;
                border-radius: 3px;
                margin: 0px 1px 3px 0px;
                &:first-child {
                    border-radius: 0px 3px 3px 0px;
                }
            }
        }
        .names-people-likes {
            margin-right: 22px;
        }
        .comments-shared {
            margin-left: 0px;
        }
        .blog-post-v3 {
            .post-content {
                padding: 25px 30px 0px;
            }
            .blog-post {
                .post-title {
                    font-weight: 300;
                    display: block;
                    margin-bottom: 15px;
                }
            }
            .description-campaign {
                padding: 0px;
                margin-bottom: 15px;
                height: 85px;
                overflow-y: auto;
            }
            .info-like {
                padding-top: 0px;
                bottom: -14px;
                margin-top: 0px;
            }
            .content {
                margin-top: 15px;
                text-align: center;
                span {
                    font-weight: bold;
                }
            }
            .information {
                font-weight: bold;
            }
        }
        .post-add-icon {
            font-size: 13px;
            fill: #219aec;
            color: #8b90aa;
            .olymp-heart-icon {
                fill: #65b2e5;
            }
            .olymp-happy-faces-icon {
                fill: #2ca7f8;
            }
            &:hover {
            fill: #c2c5d9;
            color: #8b90aa;
            }
        }
    }

    .birthday-author-name {
        margin-top: 15px;
        .author-name {
            text-transform: uppercase;
        }
    }
    .info-web {
        font-size: 0.812rem;
        text-transform: uppercase;
        margin-bottom: 10px;
        i {
            margin-right: 3px;
        }
    }
    .create-btn {
        background: #ff5e3a;
        padding: 13px 35%;
        margin-top: 10px;
    }
</style>
