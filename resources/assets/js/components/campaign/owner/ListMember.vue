<template lang="html">
    <div class="col-xl-9 push-xl-3 col-lg-9 push-lg-3 col-md-12 col-sm-12 col-xs-12">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block">
                    <div class="ui-block-title search_action">
                        <div class="row">
                            <input
                                v-model="search"
                                @input="searchMembers"
                                class="form-control input-search-action"
                                :placeholder="$t('messages.search-list-member')"
                                type="text">

                            <select
                                v-model="roleId"
                                class="form-control input-search-action"
                                @click="searchMembers">
                                <option value="0">{{ $t('form.search_all') }}</option>
                                <option v-for="role in roles" :value="role.id">{{ role.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ui-block">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('campaigns.list-members') }}</h6>
                <a href="javascript:void(0)" class="more">
                    <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                </a>
            </div>
            <div class="div-table">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="center">{{ $t('form.stt') }}</th>
                                <th class="center">{{ $t('form.name-member') }}</th>
                                <th class="center">{{ $t('form.role') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(member, index) in members.data">
                                <td class="center">{{ index + 1 }}</td>
                                <td>
                                    <div class="author-thumb">
                                        <router-link
                                            :to="{ name: 'user.timeline', params: { id: member.id }}">
                                            <img :src="member.image_thumbnail" :alt="member.name" style="height: 40px; with:40px;">
                                        </router-link>
                                    </div>
                                    <div class="notification-event">
                                        <router-link
                                            :to="{ name: 'user.timeline', params: { id: member.id }}"
                                            class="h6 notification-friend">
                                            {{ member.name }}
                                        </router-link>
                                    </div>
                                </td>
                                <td>
                                    <div class="radio col-md-3" v-for="role in roles">
                                        <label v-if="member.campaigns[0].pivot.role_id == 3 && role.id == 3">
                                            <input type="radio"
                                                :name="member.name + member.id"
                                                checked="true"
                                                :value="role.id"
                                                disabled="true">
                                            {{ role.name }}
                                        </label>

                                        <label v-else-if="role.id != 3 && member.campaigns[0].pivot.role_id != 3">
                                            <input type="hidden"
                                                v-if="role.id == member.campaigns[0].pivot.role_id"
                                                :value="roleCurrent[member.id] = member.campaigns[0].pivot.role_id">
                                            <input type="radio"
                                                :name="member.name + member.id"
                                                :id="member.name + member.id"
                                                :value="role.id"
                                                @change="blockMembers(member)"
                                                v-model="changeRole[member.id] = member.campaigns[0].pivot.role_id"
                                                :disabled="member.campaigns[0].pivot.role_id == 3">
                                            {{ role.name }}
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'
    import noty from '../../../helpers/noty'
    import Noty from 'noty'

    export default {
        data: () => ({
            members: {},
            roles: {},
            search: '',
            roleId: 0,
            changeRole: {},
            roleCurrent: {}

        }),
        created: function () {
            this.listMembers(this.$route.params.id)
        },
        updated() {
            $.material.init()
        },
        methods: {
            ...mapActions('campaign', [
                    'getListMembers',
                    'changeMemberRole',
                    'searchMember',
                    'loadMoreMembers',
                ]
            ),
            timeAgo(time) {
                return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
            },
            listMembers(campaignId) {
                this.searchMember({
                    campaignId: this.$route.params.id,
                    status: 1,
                    search: this.search,
                    roleId: this.roleId,
                    pageNumberEvent: 1,
                    pageCurrent: 0
                })
                .then(data => {
                    this.members = data.members
                    this.roles = data.roles
                })
                .catch(err => {
                    //
                })
            },
            searchMembers: _.debounce(function (e) {
                e.preventDefault()
                this.searchMember({
                    campaignId: this.$route.params.id,
                    status: 1,
                    search: this.search,
                    roleId: this.roleId,
                    pageNumberEvent: 1,
                    pageCurrent: 0
                })
                .then(data => {
                    this.members = []
                    this.members = data.members
                })
                .catch(err => {
                    //
                })
            }, 100),

            blockMembers(member) {
                let campaignId = this.$route.params.id
                console.log('current role', this.changeRole)
                var n = new Noty({
                    type: 'alert',
                    text: this.$i18n.t('messages.comfirm-block-member'),
                    layout: 'center',
                    buttons: [
                        Noty.button(this.$i18n.t('form.button.agree'), 'btn btn-success', () => {
                            n.close();
                            this.changeMemberRole({
                                campaignId: campaignId,
                                userId: member.id,
                                roleId: this.changeRole[member.id]
                            })
                            .then(status => {
                                const message = this.$i18n.t('messages.message-success')
                                noty({ text: message, force: true, type: 'success', container: false })
                            })
                            .catch(err => {
                                const message = this.$i18n.t('messages.message-fail')
                                noty({ text: message, force: true, container: false })
                            })
                        }, { id: 'button1', 'data-status': 'ok' }),

                        Noty.button(this.$i18n.t('form.button.cancel'), 'btn btn-error', () => {
                            n.close();
                            this.changeRole[member.id] = member.campaigns[0].pivot.role_id
                        })
                    ]
                }).show();
            },
        },
        mounted() {
            $(window).scroll(() => {
                if ($(document).height() <= $(window).scrollTop() + $(window).height()) {
                    this.searchMember({
                        campaignId: this.$route.params.id,
                        status: 1,
                        search: this.search,
                        roleId: this.roleId,
                        pageNumberEvent: this.members.last_page,
                        pageCurrent: this.members.current_page
                    })
                    .then(member => {
                        let list_members = this.members
                        member.members.data = [...list_members.data, ...member.members.data]
                        this.members = []
                        this.members = member.members
                    })
                    .catch(err => {
                        //
                    })
                }
            })
        },
        components: {
           //
        }
    }
</script>

<style lang="scss" scoped>
    .search_action {
        padding: 7px !important;
        .input-search-action {
            height: 42px !important;
            width: 25%;
            display: inline-block !important;
            float: left;
            padding: .8rem 2.1rem !important;
            margin-top: 2px !important;
            margin-right: 10px;
        }
        .more {
            display: inline;
        }
    }

    .center {
        text-align: center;
    }

    .div-table {
        padding: 10px;
    }
</style>
