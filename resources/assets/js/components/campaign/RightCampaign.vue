<template lang="html">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <div class="ui-block" v-if="(checkPermission || checkAdmin) && campaign.deleted_at == null">
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
            <div class="ui-block-content" v-if="listPhotos.list_action">
                <ul class="widget w-last-photo js-zoom-gallery" v-if="listPhotos.list_action.total > 0">
                    <li v-for="(photo, index) in listPhotos.list_action.data" v-if="photo.media != null && index < 8">
                        <a :href="photo.media[0].image_small">
                            <img :src="photo.media[0].image_small" alt="photo">
                        </a>
                    </li>
                    <li class="all-users" v-if="listPhotos.list_action.total > 8">
                        <router-link
                            :to="{ name: 'campaign.photo', params: { slug: campaign.id }}">
                            + {{ remainingData(listPhotos.list_action.total) }}
                        </router-link>
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
                                <li class="all-users" v-if="listMembers.members.total > 10">
                                    <a href="javascript:void(0)" @click="showListMember">
                                        + {{ remainingMembers }}
                                    </a>
                                </li>
                            </ul>

                        </div>
                    </li>
                </ul>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color c-grey full-width"
                    v-if="checkJoinCampaign == 1 && !checkPermission && campaign.deleted_at == null"
                    @click="comfirmJoinCampaign()">{{ $t('campaigns.join-now') }}</a>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color c-grey full-width"
                    v-if="checkJoinCampaign == 3 && !checkOwner && campaign.deleted_at == null"
                    @click="comfirmLeaveCampaign()" >
                    {{ $t('campaigns.leave') }}</a>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color c-grey full-width"
                    v-if="checkJoinCampaign == 2 && !checkOwner && campaign.deleted_at == null">
                    {{ $t('campaigns.aproving') }}</a>
            </div>
        </div>

        <div class="ui-block" v-if="checkPermission || checkAdmin">
            <div class="ui-block-title">
                <a href="javascript:void(0)"class="btn btn-md-2 btn-border-think custom-color c-grey full-width" v-if="campaign.deleted_at == null" @click="comfirmCloseCampaign">
                    {{ $t('campaigns.close_campaign') }}
                </a>
                <a href="javascript:void(0)"class="btn btn-md-2 btn-border-think custom-color c-grey full-width" v-else @click="comfirmOpenCampaign">
                    {{ $t('campaigns.open_campaign') }}
                </a>
            </div>
        </div>
        <!-- form comfirm close campaign -->
        <message-comfirm
            :show.sync="flag_confirm_close"
            :messages="$t('messages.comfirm-close-campaign')"
            @handelMethod="closeCampaigns">
        </message-comfirm>
        <!-- form comfirm open campaign -->
        <message-comfirm
            :show.sync="flag_confirm_open"
            :messages="$t('messages.comfirm_open_campaign')"
            @handelMethod="agreeOpen">
        </message-comfirm>
        <!-- form comfirm join campaign -->
        <message-comfirm
            :show.sync="flag_confirm_join"
            :messages="$t('messages.comfirm-join-campaign')"
            @handelMethod="joinCampaigns">
        </message-comfirm>
        <!-- form comfirm join campaign -->
        <message-comfirm
            :show.sync="flag_confirm_leave"
            :messages="$t('messages.comfirm-leave-campaign')"
            @handelMethod="leaveCampaigns">
        </message-comfirm>

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
    import noty from '../../helpers/noty'
    import Member from './Member.vue'
    import MessageComfirm from '../libs/MessageComfirm.vue'
    import { del } from '../../helpers/api'

    export default {
        created() {
            this.getlistPhotos(this.pageId)
        },
        data: () => ({
            flag_confirm_join: false,
            flag_confirm_leave: false,
            flag_show_list_member : false,
            flag_confirm_close: false,
            flag_confirm_open: false
        }),
        computed: {
            ...mapGetters('campaign', [
                'checkPermission',
                'checkJoinCampaign',
                'checkOwner'
            ]),
            ...mapState('campaign', [
                'campaign',
                'tags',
                'listMembers',
                'listPhotos'
            ]),
            ...mapState('auth', {
                authenticated: state => state.authenticated,
                user: state => state.user,
                checkAdmin: state => state.checkAdmin
            }),
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
                'getlistPhotos',
                'openCampaign',
                'campaignDetail'
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
            comfirmCloseCampaign() {
                this.flag_confirm_close = true
            },
            comfirmOpenCampaign() {
                this.flag_confirm_open = true
            },
            joinCampaigns() {
                this.attendCampaign({
                    campaignId: this.campaign.id,
                    flag: 'join'
                })
                    .then(status => {
                        this.flag_confirm_join = false
                        const message = this.$i18n.t('messages.join_campaign_success')
                        noty({ text: message, force: true, type: 'success', container: false })
                    })
                    .catch(err => {
                        this.flag_confirm_join = false
                        const message = this.$i18n.t('messages.join_campaign_fail')
                        noty({ text: message, force: true, container: false })
                    })
            },
            leaveCampaigns() {
                this.attendCampaign({
                    campaignId: this.campaign.id,
                    flag: 'leave'
                })
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
            },
            closeCampaigns() {
                del(`campaign/${this.campaign.id}`)
                    .then(res => {
                        this.flag_confirm_close = false
                        const message = this.$i18n.t('messages.close_campaign_success')
                        noty({ text: message, force: true, type: 'success', container: false })

                        this.$router.push({ name: 'user.list_campaign_close', params: {
                            slug: this.user.id
                        }})
                    })
                    .catch(err => {
                        this.flag_confirm_close = false
                        const message = this.$i18n.t('messages.close_campaign_fail')
                        noty({ text: message, force: true, container: false })
                    })
            },

            agreeOpen() {
                this.openCampaign({ campaignId: this.campaign.id })
                    .then(res => {
                        this.campaignDetail(this.pageId)
                            .then(status => {
                                //
                            })
                            .catch( err => {
                                if (err.response.data.http_status.code == 404 ||
                                    err.response.data.http_status.code == 401) {
                                    this.$router.push({ name: 'not_found' })
                                }
                            })
                    })
                    .catch(err => {
                        this.flag_confirm_open = false
                        noty({
                            text: err.response.data.http_status.messages,
                            force: true,
                            container: false
                        })
                    })

                    this.flag_confirm_open = false
            },

        },
        components: {
           Modal,
           Member,
           MessageComfirm
        }
    }
</script>

<style lang="scss">
</style>
