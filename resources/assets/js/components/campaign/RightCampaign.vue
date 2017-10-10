<template lang="html">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <div class="ui-block">
            <div class="ui-block-title">
                <router-link
                    :to="{ name: 'event.create', params: { slug: campaign.slug }}"
                    v-if="(checkPermission || checkAdmin) && !campaign.deleted_at"
                    class="btn-join btn btn-md-2 btn-border-think bg-blue full-width">
                    {{ $t('campaigns.create-event') }}
                </router-link>
                <a href="javascript:void(0)"
                    class="btn marginless btn-md-2 btn-border-think custom-color bg-grey full-width"
                    v-if="checkOwner && !campaign.deleted_at"
                    @click="comfirmCloseCampaign">
                    {{ $t('campaigns.close_campaign') }}
                </a>
                <a href="javascript:void(0)"
                    class="btn marginless btn-md-2 btn-border-think custom-color bg-primary full-width"
                    v-else-if="checkOwner" @click="comfirmOpenCampaign">
                    {{ $t('campaigns.open_campaign') }}
                </a>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color bg-primary full-width"
                    v-if="checkJoinCampaign == 1 && !checkPermission && !campaign.deleted_at"
                    @click="comfirmJoinCampaign">{{ $t('campaigns.join-now') }}</a>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color bg-grey full-width"
                    v-if="checkJoinCampaign == 3 && !checkOwner && !campaign.deleted_at"
                    @click="comfirmLeaveCampaign" >
                    {{ $t('campaigns.leave') }}</a>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color bg-purple full-width"
                    v-if="checkJoinCampaign == 2 && !checkOwner && !campaign.deleted_at">
                    {{ $t('campaigns.aproving') }}</a>

                <a href="javascript:void(0)" class="btn btn-md-2 btn-border-think custom-color bg-purple full-width"
                    @click="comfirmAcceptCampaign"
                    v-if="checkJoinCampaign == 4 && !checkOwner && !campaign.deleted_at">
                    {{ $t('campaigns.accept') }}</a>
            </div>
        </div>

        <div class="ui-block">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('campaigns.total_tags') }}</h6>
                <a href="javascript:void(0)" class="more" style="display: none;">
                    <svg class="olymp-three-dots-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                    </svg>
                </a>
            </div>
            <div class="ui-block-content">
                <span v-for="tag, index in tags" class="div-tags">
                    {{ tag.name }}
                </span>
            </div>
        </div>

        <div class="ui-block" v-if="listPhotos.list_action">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('campaigns.typical-images') }}</h6>
                <a href="javascript:void(0)" class="more" style="display: none;">
                    <svg class="olymp-three-dots-icon">
                    <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
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
                            :to="{ name: 'campaign.photo', params: { slug: campaign.slug }}">
                            + {{ remainingData(listPhotos.list_action.total) }}
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>

        <div class="ui-block">
            <member :flagShowListMember.sync="flag_show_list_member" v-if="checkInvite()"></member>
            <div class="ui-block-title">
                <h6 class="title">{{ $t('campaigns.list-members') }}</h6>
                <a href="javascript:void(0)" class="more" style="display: none;">
                    <svg class="olymp-three-dots-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                    </svg>
                </a>
            </div>
            <div class="w-pool-content ui-block-content">
                <ul class="widget w-pool">
                    <li>
                        <div class="skills-item">
                            <div class="counter-friends">{{ listMembers.members.total }} {{ $t('campaigns.lbl-members') }}</div>
                            <ul class="widget w-faved-page js-zoom-gallery" v-if="listMembers.members.total > 0">
                                <li
                                    v-for="(member, index) in listMembers.members.data"
                                    v-if="index < 10"
                                    v-tooltip:top="member.name">
                                    <router-link :to="{ name: 'user.timeline', params: { slug: member.slug }}">
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

            </div>
        </div>
        <!-- invite user to join campaign -->
        <invite-member v-if="checkInvite()"></invite-member>
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
        <!-- form comfirm accept join to this campaign -->
        <message-comfirm
            :show.sync="flag_confirm_accept"
            :messages="$t('messages.comfirm-accept-campaign')"
            @handelMethod="acceptCampaigns">
        </message-comfirm>

    </div>
</template>

<script>
    import { mapState, mapActions, mapGetters } from 'vuex'
    import noty from '../../helpers/noty'
    import Member from './Member.vue'
    import MessageComfirm from '../libs/MessageComfirm.vue'
    import { del, post } from '../../helpers/api'
    import InviteMember from './InviteMember.vue'

    export default {
        created() {
            this.getlistPhotos(this.pageId)
        },
        data: () => ({
            flag_confirm_join: false,
            flag_confirm_leave: false,
            flag_show_list_member: false,
            flag_confirm_close: false,
            flag_confirm_open: false,
            flag_confirm_accept: false
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
                'campaignDetail',
                'acceptCampaign',
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
            comfirmAcceptCampaign() {
                this.flag_confirm_accept = true
            },
            joinCampaigns() {
                this.attendCampaign({
                    campaignId: this.pageId,
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
                    campaignId: this.pageId,
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
                del(`campaign/${this.pageId}`)
                    .then(res => {
                        this.flag_confirm_close = false
                        const message = this.$i18n.t('messages.close_campaign_success')
                        noty({ text: message, force: true, type: 'success', container: false })

                        this.$router.push({
                            name: 'user.list_campaign_close',
                            params: {
                                slug: this.user.slug
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
                        this.flag_confirm_open = false
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
            },
            acceptCampaigns() {
                this.acceptCampaign(this.pageId)
                    .then(res => {
                        this.flag_confirm_accept = false
                        const message = this.$i18n.t('messages.accept-campaign-succesfully')
                        noty({ text: message, force: true, type: 'success', container: false })
                    })
                    .catch(err => {
                        this.flag_confirm_accept = false
                        const message = this.$i18n.t('messages.fail')
                        noty({ text: message, force: true, container: false })
                    })
            },
            checkInvite() {
                if (this.campaign.status && !this.campaign.deleted_at) {
                    if ((this.campaign.status['value'] == 0 && (this.checkPermission || this.checkAdmin))
                        || (this.campaign.status['value'] == 1 && (this.checkJoinCampaign == 3 || this.checkAdmin)))  {
                        return true;
                    }

                    return false;
                }
            }
        },
        components: {
           Modal,
           Member,
           MessageComfirm,
           InviteMember
        }
    }
</script>

<style lang="scss" scoped>
    .div-tags {
        display: inline-block;
        background: #3f4257;
        padding: 5px 10px;
        border-radius: 3px;
        color: #fff;
        margin: 3px 0px 0px 3px;
    }

    .ui-block-title {
        .btn + * {
            margin-left: 0px;
        }
    }

    .w-pool-content {
        padding: 15px 25px 10px;
        .skills-item {
            margin-bottom: 0px;

            img {
                width: 34px;
                height: 34px;
            }
        }

        .btn-join {
            margin-bottom: 5px;
        }

        .w-pool {
            margin-bottom: 20px;
        }
    }
    .marginless {
        margin-left: 0;
    }
</style>
