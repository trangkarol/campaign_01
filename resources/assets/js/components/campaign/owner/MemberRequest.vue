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
                                    :placeholder="$t('messages.search')"
                                    type="text">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ui-block">
                <div class="ui-block-title">
                    <h6 class="title">{{ $t('campaigns.member-request') }}</h6>
                    <a href="javascript:void(0)" class="more">
                        <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                    </a>
                </div>
                <ul class="notification-list friend-requests">
                    <li v-for="member in members.data">
                        <div class="author-thumb">
                            <router-link
                                :to="{ name: 'user.timeline', params: { slug: member.id }}">
                                <img :src="member.image_thumbnail" :alt="member.name" style="height: 40px; with:40px;">
                            </router-link>
                        </div>
                        <div class="notification-event">
                            <router-link
                                :to="{ name: 'user.timeline', params: { slug: member.id }}"
                                class="h6 notification-friend">
                                {{ member.name }}
                            </router-link>
                            <span class="chat-message-item">{{ timeAgo(member.created_at) }}</span>
                        </div>
                        <span class="notification-icon" v-if="member.deleted_at == null">
                            <a href="javascript:void(0)" @click="approveMembers(member.id)" class="accept-request">
                                <span class="icon-add">
                                    <svg class="olymp-happy-face-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use></svg>
                                </span>
                                {{ $t('campaigns.accept-request') }}
                            </a>

                            <a href="javascript:void(0)" @click="rejectMembers(member.id)" class="accept-request request-del">
                                <span class="icon-minus">
                                    <svg class="olymp-happy-face-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use></svg>
                                </span>
                            </a>
                        </span>
                    </li>
                </ul>
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
            search: ''
        }),
        created: function () {
            this.listMembers(this.pageId)
        },
        methods: {
            ...mapActions('campaign', [
                'getListMembers',
                'changeStatusMember',
                'searchMember',
                'loadMoreMembers',
            ]),
            timeAgo(time) {
                return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
            },
            listMembers(campaignId) {
                this.getListMembers({ campaignId: campaignId, status: 0 })
                    .then(data => {
                        this.members = data
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
                    status: 0,
                    search: this.search,
                    pageNumberEvent: 1,
                    pageCurrent: 0
                })
                .then(data => {
                    this.members = []
                    this.members = data
                })
                .catch(err => {
                    //
                })
            }, 100),
            approveMembers(userId) {
                let campaignId = this.pageId

                var n = new Noty({
                    type: 'alert',
                    text: this.$i18n.t('messages.comfirm-accept-request'),
                    layout: 'center',
                    modal: true,
                    buttons: [
                        Noty.button(this.$i18n.t('form.button.agree'), 'btn-upper btn btn-primary btn--half-width', () => {
                            n.close();

                            this.changeStatusMember({
                                campaignId: campaignId,
                                userId: userId,
                                flag: 'approve'
                            })
                            .then(status => {
                                const message = this.$i18n.t('messages.message-success')
                                noty({ text: message, force: true, type: 'success', container: false })
                                this.changeDataMember(this.members.data, userId)
                            })
                            .catch(err => {
                                const message = this.$i18n.t('messages.message-fail')
                                noty({ text: message, force: true, container: false })
                            })
                        }, { id: 'button1', 'data-status': 'ok' }),

                        Noty.button(this.$i18n.t('form.button.cancel'), 'btn-upper btn btn-secondary btn--half-width', function () {
                            n.close();
                        })
                    ]
                }).show();

            },
            rejectMembers(userId) {
                let campaignId = this.pageId

                var n = new Noty({
                    type: 'alert',
                    text: this.$i18n.t('messages.comfirm-cancel-request'),
                    layout: 'center',
                    buttons: [
                        Noty.button(this.$i18n.t('form.button.agree'), 'btn btn-success', () => {
                            n.close();

                            this.changeStatusMember({
                                campaignId: campaignId,
                                userId: userId,
                                flag: 'reject'
                            })
                            .then(status => {
                                const message = this.$i18n.t('messages.message-success')
                                noty({ text: message, force: true, type: 'success', container: false })
                                this.changeDataMember(this.members.data, userId)
                            })
                            .catch(err => {
                                const message = this.$i18n.t('messages.message-fail')
                                noty({ text: message, force: true, container: false })
                            })
                        }, { id: 'button1', 'data-status': 'ok' }),

                        Noty.button(this.$i18n.t('form.button.cancel'), 'btn btn-error', function () {
                            n.close();
                        })
                    ]
                }).show();

            },
            changeDataMember(members, userId) {
                let data = $.grep(members, function (member) {
                    return member.id != userId;
                });

                this.members.data = data
            }
        },
        mounted() {
            $(window).scroll(() => {
                if ($(document).height() <= $(window).scrollTop() + $(window).height()) {
                    this.searchMember({
                        campaignId: this.pageId,
                        status: 0,
                        search: this.search,
                        pageNumberEvent: this.members.last_page,
                        pageCurrent: this.members.current_page
                    })
                    .then(member => {
                        let list_members = this.members
                        member.data = [...list_members.data, ...member.data]
                        this.members = []
                        this.members = member
                    })
                    .catch(err => {
                        //
                    })
                }
            })
        },
    }
</script>

<style lang="scss">
</style>
