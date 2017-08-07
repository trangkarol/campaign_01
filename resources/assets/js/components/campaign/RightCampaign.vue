<template lang="html">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <div class="ui-block" v-if="checkPermission">
            <div class="ui-block-title">
                <router-link
                    :to="{ name: 'event.create', params: { campaign_id: campaign.id }}"
                    class="btn btn-md-2 btn-border-think custom-color c-grey full-width">
                    {{ $t('campaigns.create-event') }}
                </router-link>
            </div>
        </div>

        <div class="ui-block">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('campaigns.tags') }}</h6>
                <a href="javascript:void(0)" class="more">
                    <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                </a>
            </div>
            <div class="ui-block-content">
                <p v-for="tag, index in tags" class="div-tags">
                    <span class="tag is-info">
                        {{ tag.name }}
                    </span>
                </p>
            </div>
        </div>

        <div class="ui-block">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('campaigns.typical-images') }}</h6>
                <a href="javascript:void(0)" class="more">
                    <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                </a>
            </div>
            <div class="ui-block-content">

                <ul class="widget w-last-photo js-zoom-gallery" v-if="listPhotos.total > 0">
                    <li v-for="photo in listPhotos.data" >
                        <a href="#" v-if="photo.media != null">
                            <img :src="photo.media[0].image_small" alt="photo">
                        </a>
                    </li>
                    <li class="all-users" v-if="listPhotos.total > 8">
                        <a href="javascript:void(0)">+ {{ remainingData(listPhotos.total) }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="ui-block">
            <member :flagShowListMember.sync="flag_show_list_member"></member>
            <div class="ui-block-title">
                <h6 class="title">{{ $t('campaigns.list-members') }}</h6>
                <a href="javascript:void(0)" class="more">
                    <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                </a>
            </div>
            <div class="ui-block-content">
                <ul class="widget w-pool">
                    <li>
                        <div class="skills-item">
                            <div class="counter-friends">{{ listMembers.members.total }} {{ $t('campaigns.lbl-members') }}</div>
                            <ul class="widget w-faved-page js-zoom-gallery" v-if="listMembers.members.total > 0">
                                <li v-for="(member, index) in listMembers.members.data" v-if="index < 10">
                                    <router-link :to="{ name: 'user.timeline', params: { id: member.id }}">
                                        <img :src="member.image_thumbnail" :alt="member.name">
                                    </router-link>
                                </li>
                                <li class="all-users" v-if="listMembers.members.total >= 10">
                                    <a href="javascript:void(0)" @click="showListMember">
                                        + {{ remainingMembers }}
                                    </a>
                                </li>
                            </ul>

                        </div>
                    </li>
                </ul>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color c-grey full-width"
                    v-if="checkJoinCampaign == 1 && !checkPermission"
                    @click="comfirmJoinCampaign()">{{ $t('campaigns.join-now') }}</a>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color c-grey full-width"
                    v-if="checkJoinCampaign == 3 && !checkPermission"
                    @click="comfirmLeaveCampaign()" >
                    {{ $t('campaigns.leave') }}</a>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color c-grey full-width"
                    v-if="checkJoinCampaign == 2 && !checkPermission">
                    {{ $t('campaigns.aproving') }}</a>
            </div>
        </div>
        <!-- form comfirm join campaign -->
        <modal :show.sync="flag_confirm_join">
            <h5 class="exclamation-header" slot="header">
                {{ $t('messages.comfirm-join-campaign') }}
            </h5>
            <div class="body-modal" slot="main">
                <a href="javascript:void(0)"
                    class="btn btn-breez col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="joinCampaigns">
                    {{ $t('form.button.agree') }}
                </a>
                <a href="javascript:void(0)"
                    class="btn btn-secondary col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="cancelJoinCampaign">
                    {{ $t('form.button.cancel') }}
                </a>
            </div>
        </modal>

        <!-- form comfirm leave campaign -->
        <modal :show.sync="flag_confirm_leave">
            <h5 class="exclamation-header" slot="header">
                {{ $t('messages.comfirm-leave-campaign') }}
            </h5>
            <div class="body-modal" slot="main">
                <a href="javascript:void(0)"
                    class="btn btn-breez col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="leaveCampaigns">
                    {{ $t('form.button.agree') }}
                </a>
                <a href="javascript:void(0)"
                    class="btn btn-secondary col-lg-3 col-md-6 col-sm-12 col-xs-12"
                    @click="cancelLeaveCampaign">
                    {{ $t('form.button.cancel') }}
                </a>
            </div>
        </modal>
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
    import { mapState, mapActions, mapGetters } from 'vuex'
    import Modal from '../libs/Modal.vue'
    import noty from '../../helpers/noty'
    import Member from './Member.vue'

    export default {
        created() {
            this.getlistPhotos(this.$route.params.id)
        },
        data: () => ({
            flag_confirm_join: false,
            flag_confirm_leave: false,
            flag_show_list_member : false
        }),
        computed: {
            ...mapGetters('campaign', [
                'checkPermission',
                'checkJoinCampaign',
            ]),
            ...mapState('campaign', [
                'campaign',
                'tags',
                'listMembers',
                'listPhotos'
            ]),
            ...mapState('auth', {
                authenticated: state => state.authenticated,
                user: state => state.user
            }),
            // totalMemberCurrent() {
            //     if (this.listMembers.members != null) {
            //         return this.listMembers.members.length
            //     }

            //     return 0

            // },
            remainingMembers() {
                if (this.listMembers.members != null) {
                    return parseInt(this.listMembers.members.total) - 10
                }

                return 0
            }
        },
        methods: {
            ...mapActions('campaign', [
                'attendCampaign',
                'getlistPhotos'
            ]),
            showListMember() {
                this.flag_show_list_member = true
            },
            comfirmJoinCampaign() {
                this.flag_confirm_join = true
            },
            comfirmLeaveCampaign() {
                this.flag_confirm_leave = true
            },
            cancelJoinCampaign() {
                this.flag_confirm_join = false
            },
            cancelLeaveCampaign() {
                this.flag_confirm_leave = false
            },
            joinCampaigns() {
                this.attendCampaign({ campaignId: this.campaign.id, flag: 'join' })
                    .then(status => {
                        this.flag_confirm_join = false
                        const message = this.$i18n.t('messages.join_campaign_success')
                        noty({ text: message, force: true, type: 'success', container: false })
                    })
                    .catch(err => {
                        console.log('errr', err)
                        this.flag_confirm_join = false
                        const message = this.$i18n.t('messages.join_campaign_fail')
                        noty({ text: message, force: true, container: false })
                    })
            },
            leaveCampaigns() {
                this.attendCampaign({ campaignId: this.campaign.id, flag: 'leave' })
                    .then(status => {
                        this.flag_confirm_leave = false
                        const message = this.$i18n.t('messages.leave_campaign_success')
                        noty({ text: message, force: true, type: 'success', container: false })
                    })
                    .catch(err => {
                        this.flag_confirm_leave = false
                        const message = this.$i18n.t('messages.leave_campaign_fail')
                        noty({ text: message, force: true, container: false })
                    })
            },
            remainingData(data) {
                return data - 8
            },
            checkStausJoinCampaign(userId) {
                let checkStatus = false
                    this.listMembers.members.forEach(function(item, index){
                        if (item.id == userId && item.pivot.status == 1) {
                            checkStatus = true
                        }
                    });

                return checkStatus
            }
        },
        components: {
           Modal,
           Member
        }
    }
</script>

<style lang="scss">
</style>
