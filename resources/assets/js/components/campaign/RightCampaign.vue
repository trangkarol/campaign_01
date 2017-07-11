<template lang="html">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.9/semantic.min.css" rel="stylesheet">
        <div class="ui-block">
            <div class="ui-block-title">
                <router-link :to="{ path: '/event/create/', params: { campaignId: campaign.id }}" class="btn btn-md-2 btn-border-think custom-color c-grey full-width">{{ $t('campaigns.create-event') }}</router-link>
            </div>
        </div>

        <div class="ui-block">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('campaigns.tags') }}</h6>
                <a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
            </div>
            <div class="ui-block-content">
                    <p v-for="tag, index in tags" class="div-tags">
                        <span class="tag is-info">
                            {{ tag.name }}
                        </span>
                    </p>
                </ol>
            </div>
        </div>

        <div class="ui-block">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('campaigns.typical-images') }}</h6>
                <a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
            </div>
            <div class="ui-block-content">
                <ul class="widget w-last-photo js-zoom-gallery">
                    <li>
                        <a href="/images/last-photo1-large.jpg">
                            <img src="/images/last-photo1-large.jpg" alt="photo">
                        </a>
                    </li>

                    <li>
                        <a href="/images/last-photo1-large.jpg">
                            <img src="/images/last-photo1-large.jpg" alt="photo">
                        </a>
                    </li>
                </ul>
            </div>
        </div>

            <div class="ui-block">
                <div class="ui-block-title">
                    <h6 class="title">{{ $t('campaigns.list-members') }}</h6>
                    <a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
                </div>
                <div class="ui-block-content">
                    <ul class="widget w-pool">
                        <li>
                            <div class="skills-item">
                                <div class="counter-friends">{{ totalMemberCurrent }} {{ $t('campaigns.lbl-members') }}</div>

                                <ul class="friends-harmonic">
                                    <li v-for="member in campaign.members">
                                        <a href="#">
                                            <img :src="member.url_file" :alt="member.name">
                                        </a>
                                    </li>
                                    <li v-if="totalMemberCurrent >= 10">
                                        <a href="#" class="all-users">+ {{ remainingMembers }}</a>
                                    </li>
                                </ul>

                            </div>
                        </li>
                    </ul>
                    <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color c-grey full-width" v-if="campaign.memberIds.indexOf(user.id) < 0" @click="joinCampaign()">{{ $t('campaigns.join-now') }}</a>
                    <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color c-grey full-width" v-if="campaign.memberIds.indexOf(user.id) >= 0" @click="leaveCampaign()">{{ $t('campaigns.leave') }}</a>
                </div>
            </div>
        </div>
</template>

<style>
    span.tag.is-info {
        background: #2185d0;
        border-color: #2185d0;
        padding: 3%;
        border-radius: 3px;
        color: #fff;
        margin-right: 2px;
    }

    .div-tags {
        padding:1%;
    }
</style>

<script>
    import { mapState } from 'vuex'

    export default {
        computed: {
            ...mapState('campaign', ['campaign', 'tags']),
            ...mapState('auth', {
                authenticated: state => state.authenticated,
                user: state => state.user
            }),
            totalMemberCurrent() {
                if (this.campaign.members != null) {
                    return this.campaign.members.length
                }

                return 0

            },
            remainingMembers() {
                if (this.campaign.members != null) {
                    return parseInt(this.campaign.members.length) - 10
                }

                return 0
            }
        },
        methods: {
            ...mapActions('campaign', ['joinCampaign', 'leaveCampaign']),
            comfirmJoinCampaign() {
                this.flag_confirm_join = true
            },
            comfirmLeaveCampaign() {
                this.flag_confirm_leave = true
            },
            cancelJoinCampaign() {
                this.flag_confirm_join = false
            },
            joinCampaigns() {
                this.joinCampaign(this.campaign.id)
                    .then(status => {
                        if (status) {
                            this.flag_confirm_join = false
                            const message = this.$i18n.t('messages.join_campaign_success')
                            noty({ text: message, force: true, type: 'success', container: false })
                        }
                    })
                    .catch(err => {
                        this.flag_confirm_join = false
                        const message = this.$i18n.t('messages.join_campaign_fail')
                        noty({ text: message, force: true, container: false })
                    })
            },
            leaveCampaigns() {
                this.leaveCampaign(this.campaign.id)
                    .then(status => {
                        if (status) {
                            this.flag_confirm_leave = false
                            const message = this.$i18n.t('messages.leave_campaign_success')
                            noty({ text: message, force: true, type: 'success', container: false })
                        }
                    })
                    .catch(err => {
                        this.flag_confirm_leave = false
                        const message = this.$i18n.t('messages.leave_campaign_fail')
                        noty({ text: message, force: true, container: false })
                    })
            }
        },
        components: {
           Modal
        }
    }
</script>

<style lang="scss">
</style>
