<template lang="html">
    <div class="col-xl-9 push-xl-3 col-lg-9 push-lg-3 col-md-12 col-sm-12 col-xs-12">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block">
                    <div class="ui-block-title search_action">
                        <div class="search row">
                            <div class="col-md-6">
                                 <input
                                    v-model="search"
                                    @input="searchMembers"
                                    class="form-control input-search-action"
                                    :placeholder="$t('messages.search-list-member')"
                                    type="text">
                            </div>
                            <div class="select col-md-6">
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
                                            :to="{ name: 'user.timeline', params: { slug: member.id }}">
                                            <img :src="member.image_thumbnail" :alt="member.name" style="height: 40px; with:40px;">
                                        </router-link>
                                    </div>
                                    <div class="name-member">
                                        <router-link
                                            :to="{ name: 'user.timeline', params: { slug: member.id }}"
                                            class="h6 notification-friend">
                                            {{ member.name }}
                                        </router-link>
                                    </div>
                                </td>
                                <td>
                                    <div class="radio" v-for="role in roles">
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
                                                :value="roleCurrent[member.id] = role.id">
                                            <input type="radio"
                                                :name="member.name + member.id"
                                                :value="role.id"
                                                :data-id="role.id"
                                                :data-user-id="member.id"
                                                :data-deleted="member.deleted_at"
                                                @change="changeRoleMember"
                                                :checked="member.campaigns[0].pivot.role_id === role.id"
                                                :disabled="member.campaigns[0].pivot.role_id == 3 || member.id == user.id">
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
    import { mapActions, mapState } from 'vuex'
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
            this.listMembers(this.pageId)
        },
        updated() {
            $.material.init()
        },
        computed: {
            ...mapState('auth', [
                'user'
            ])
        },
        methods: {
            ...mapActions('campaign', [
                'getListMembers',
                'changeMemberRole',
                'searchMember',
                'loadMoreMembers',
            ]),
            timeAgo(time) {
                return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
            },
            listMembers(campaignId) {
                this.searchMember({
                    campaignId: this.pageId,
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
                    const message = this.$i18n.t('messages.message-fail')
                    noty({ text: message, force: true, container: false })
                })
            },
            searchMembers: _.debounce(function (e) {
                e.preventDefault()
                this.searchMember({
                    campaignId: this.pageId,
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

            changeRoleMember(e) {
                let campaignId = this.pageId
                let target = $(e.currentTarget)
                let userId = target.attr('data-user-id')
                let deleteDate = target.attr('data-deleted')
                console.log('dsdsd', deleteDate)
                if (deleteDate == null) {
                    var n = new Noty({
                        type: 'alert',
                        text: this.$i18n.t('messages.comfirm-block-member'),
                        layout: 'center',
                        closeWith: 'button',
                        modal: true,
                        buttons: [
                            Noty.button(this.$i18n.t('form.button.agree'), 'btn-upper btn btn-primary btn--half-width', () => {
                                n.close();
                                this.changeMemberRole({
                                    campaignId: campaignId,
                                    userId: userId,
                                    roleId: target.attr('data-id')
                                })
                                .then(status => {
                                    const message = this.$i18n.t('messages.message-success')
                                    noty({ text: message, force: true, type: 'success', container: false })
                                })
                                .catch(err => {
                                    const message = this.$i18n.t('messages.message-fail')
                                    noty({ text: message, force: true, container: false })
                                    target.prop('checked', false)
                                })
                            }, { id: 'button1', 'data-status': 'ok' }),

                            Noty.button(this.$i18n.t('form.button.cancel'), 'btn-upper btn btn-secondary btn--half-width', () => {
                                target.prop('checked', false)
                                this.members.data.forEach( (item, index) => {
                                    if (item.id == userId) {
                                        item.campaigns[0].pivot.role_id = []
                                        item.campaigns[0].pivot.role_id = this.roleCurrent[userId]
                                    }
                                })

                                n.close();
                            })
                        ]
                    }).show();
                }

            },
        },
        mounted() {
            $(window).scroll(() => {
                if ($(document).height() <= $(window).scrollTop() + $(window).height()) {
                    this.searchMember({
                        campaignId: this.pageId,
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
    }
</script>

<style lang="scss" scoped>
    .search_action {
        padding: 7px !important;
        .search {
            padding-bottom: 18px;
        }
        .select {
            float: left;
        }
        .input-search-action {
            height: 42px !important;
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
    .div-table {
        padding: 10px;
        .radio {
            display: inherit;
        }
        .center {
            text-align: center;
            padding-top: 25px;
        }
        .name-member {
            display: inline-block;
            margin-left: 10px;
        }
        td:nth-child(3) {
            padding-top: 25px;
            width: 40%;
        }
        thead {
            background: #eceeef;
        }
    }
</style>
