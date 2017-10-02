<template lang="html">
    <div class="ui-block" v-if="!campaign.deleted_at">
        <div class="ui-block-title">
            <h6 class="title">{{ $t('campaigns.invite_user') }}</h6>
        </div>
        <div class="invite-user ui-block-content">
            <div class="searchbox">
                <input
                    type="text"
                    class="form-control"
                    v-model="search_invite"
                    @input="searchMemberToInvites"
                    :placeholder="$t('messages.search')">
                    <i class="fa fa-search" aria-hidden="true"></i>
            </div>
            <div class="div-invite-user">
                <ul class="widget w-friend-pages-added notification-list friend-requests" v-if="listInviteUser">
                    <li class="inline-items" v-for="(member, index) in listInviteUser.data">
                        <div class="author-thumb">
                            <router-link
                                :to="{ name: 'user.timeline', params: { slug: member.slug }}">
                                <img :src="member.image_thumbnail" :alt="member.name" class="img-member">
                            </router-link>
                        </div>
                        <div class="notification-event name-member">
                            <router-link
                                :to="{ name: 'user.timeline', params: { slug: member.slug }}"
                                class="h6 notification-friend">
                                {{ member.name }}
                            </router-link>
                            <span class="chat-message-item">{{ member.email }}</span>
                        </div>
                        <span class="notification-icon">
                            <a href="javascript:void(0)" class="accept-request" @click="comfirmRequest(member.id)">
                                <span class="icon-add without-text">
                                    <svg class="olymp-happy-face-icon">
                                        <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                                    </svg>
                                </span>
                            </a>
                        </span>
                    </li>
                    <li class="no-data inline-items" v-if="!listInviteUser.data.length">
                        {{ $t('campaigns.no_data') }}
                    </li>
                </ul>
            </div>
        </div>
        <!-- form comfirm invite member to join this campaign -->
        <message-comfirm
            :show.sync="flag_confirm_invite"
            :messages="$t('messages.comfirm-invite-member')"
            @handelMethod="inviteCampaign">
        </message-comfirm>
    </div>
</template>

<script>
    import { mapActions, mapState, mapGetters } from 'vuex'
    import noty from '../../helpers/noty'
    import MessageComfirm from '../libs/MessageComfirm.vue'
    import Noty from 'noty'

    export default {
        data: () => ({
            search_invite: '',
            listInviteUser: null,
            flag_confirm_invite: false,
            userIdCurrent: 0
        }),
        created: function () {
            if (this.checkInvite()) {
                this.searchMemberToInvite({
                    campaignId: this.pageId,
                    search: this.search_invite,
                    pageNumberEvent: 1,
                    pageCurrent: 0
                })
                .then(data => {
                    this.listInviteUser = []
                    this.listInviteUser = data.members
                })
                .catch(err => {
                    const message = this.$i18n.t('messages.message-fail')
                    noty({ text: message, force: true, container: false })
                })
            }
        },
        computed: {
            ...mapState('campaign', [
                'campaign',
            ]),
            ...mapGetters('campaign', [
                'checkPermission',
                'checkJoinCampaign',
                'checkOwner'
            ]),
        },
        methods: {
            ...mapActions('campaign', [
                'searchMemberToInvite',
                'inviteUser',
            ]),
            searchMemberToInvites: _.debounce(function (e) {
                e.preventDefault()
                this.searchMemberToInvite({
                    campaignId: this.pageId,
                    search: this.search_invite,
                    pageNumberEvent: 1,
                    pageCurrent: 0
                })
                .then(data => {
                    this.listInviteUser = []
                    this.listInviteUser = data.members
                })
                .catch(err => {
                    const message = this.$i18n.t('messages.message-fail')
                    noty({ text: message, force: true, container: false })
                })
            }, 100),
            comfirmRequest(userId) {
                this.flag_confirm_invite = true
                this.userIdCurrent = userId
            },
            inviteCampaign() {
                let data = {
                    campaignId: this.pageId,
                    userId: this.userIdCurrent
                }

                this.inviteUser(data)
                    .then(status => {
                        const message = this.$i18n.t('messages.message-success')
                        noty({ text: message, force: true, type: 'success', container: false })
                    this.flag_confirm_invite = false
                    this.changeDataMember(this.listInviteUser.data, data.userId)
                    this.userIdCurrent = 0
                    })
                    .catch(err => {
                        const message = this.$i18n.t('messages.message-fail')
                        noty({ text: message, force: true, container: false })
                        this.flag_confirm_invite = false
                        this.userIdCurrent = 0
                    })
            },
            changeDataMember(members, userId) {
                let data = $.grep(members, function (member) {
                    return member.id != userId;
                });

                this.listInviteUser.data = data
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
        mounted() {
            $('.div-invite-user').scroll(() => {
                if ($('.div-invite-user').scrollTop() + $('.div-invite-user').innerHeight()
                    >= $('.div-invite-user')[0].scrollHeight) {
                    this.searchMemberToInvite({
                        campaignId: this.pageId,
                        search: this.search_invite,
                        pageNumberEvent: this.listInviteUser.last_page,
                        pageCurrent: this.listInviteUser.current_page
                    })
                    .then(data => {
                        if (typeof(data) !== "undefined") {
                            let list_members = this.listInviteUser
                            data.members.data = [...list_members.data, ...data.members.data]
                            this.listInviteUser = []
                            this.listInviteUser = data.members
                        }
                    })
                    .catch(err => {
                        const message = this.$i18n.t('messages.message-fail')
                        noty({ text: message, force: true, container: false })
                    })
                }
            })
        },
         components: {
           MessageComfirm,
        }
    }
</script>

<style lang="scss" scoped>
    .invite-user {
        padding: 20px 15px 20px;
        .div-invite-user {
            margin-top: 15px;
            border: 1px solid #e6ecf5;
            border-radius: 5px;
            overflow-y: auto;
            height: 300px;

            .notification-list {
                margin-bottom: 0;
                li {
                    padding: 15px 10px;
                    &:last-child {
                        border: 0;
                    }
                }
                .no-data {
                    text-align: center;
                    font-size: 14px;
                }
            }

            &::-webkit-scrollbar-track
            {
                -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
                background-color: #F5F5F5;
            }

            &::-webkit-scrollbar
            {
                width: 2px;
                background-color: #ff5e3a;
            }

            &::-webkit-scrollbar-thumb
            {
                background-color: #494c62;
            }
        }
    }
    .img-member {
        width: 40px !important;
        height: 40px !important;
    }

    .name-member {
        max-width: 55% !important;
    }

    .searchbox {
        position: relative;
        .form-control {
            padding: 10px 15px;
        }
        i {
            position: absolute;
            top: 11px;
            font-size: 20px;
            right: 15px;
        }
    }
</style>
