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
            <ul class="notification-list friend-requests" v-if="members.total">
                <li v-for="member in members.data">
                    <div class="author-thumb">
                        <router-link
                            :to="{ name: 'user.timeline', params: { slug: member.slug }}">
                            <img :src="member.image_thumbnail" :alt="member.name" class="img-member">
                        </router-link>
                    </div>
                    <div class="notification-event">
                        <router-link
                            :to="{ name: 'user.timeline', params: { slug: member.slug }}"
                            class="h6 notification-friend">
                            {{ member.name }}
                        </router-link>
                        <span class="chat-message-item">{{ member.email }}</span>
                    </div>
                    <span class="notification-icon" v-if="!campaign.deleted_at">
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
            <ul class="notification-list friend-requests" v-else>
                <li>
                    {{ $t('messages.no_member_show') }}
                </li>
            </ul>
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
            search: ''
        }),
        created: function () {
            this.listMembers()
        },
        computed: {
            ...mapState('campaign', [
                'campaign',
            ]),
        },
        methods: {
            ...mapActions('campaign', [
                'getListMembers',
                'changeStatusMember',
                'searchMember',
                'loadMoreMembers',
                'listMember',
            ]),
            timeAgo(time) {
                return moment(time, "YYYY-MM-DD h:mm:ss").fromNow()
            },
            listMembers() {
                this.listMember({
                    campaignId: this.pageId,
                    status: 0,
                    search: this.search,
                    pageNumberEvent: 1,
                    pageCurrent: 0
                })
                .then(data => {
                    this.members = data.members
                })
                .catch(err => {
                    if (err.response.data.http_status.code == 404 ||
                        err.response.data.http_status.code == 401) {
                        this.$router.push({ name: 'not_found' })
                    }
                })
            },
            searchMembers: _.debounce(function (e) {
                e.preventDefault()
                this.listMember({
                    campaignId: this.pageId,
                    status: 0,
                    search: this.search,
                    pageNumberEvent: 1,
                    pageCurrent: 0
                })
                .then(data => {
                    this.members = []
                    this.members = data.members
                })
                .catch(err => {
                    if (err.response.data.http_status.code == 404 ||
                        err.response.data.http_status.code == 401) {
                        this.$router.push({ name: 'not_found' })
                    }
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

                this.members.total -= 1
                this.members.data = data
            }
        },
        mounted() {
            $(window).scroll(() => {
                if ($(document).height() <= $(window).scrollTop() + $(window).height()) {
                    if ((parseInt(this.members.current_page) + 1) <= this.members.last_page) {
                        this.listMember({
                            campaignId: this.pageId,
                            status: 0,
                            search: this.search,
                            pageNumberEvent: this.members.last_page,
                            pageCurrent: this.members.current_page
                        })
                        .then(data => {
                            let list_members = this.members
                            data.members.data = [...list_members.data, ...data.members.data]
                            this.members = []
                            this.members = data.members
                        })
                        .catch(err => {
                            if (err.response.data.http_status.code == 404 ||
                                err.response.data.http_status.code == 401) {
                                this.$router.push({ name: 'not_found' })
                            }
                        })
                    }
                }
            })
        },
        beforeDestroy() {
            $(window).off()
        },
    }
</script>

<style lang="scss" scoped>
    .img-member {
        width: 40px !important;
        height: 40px !important;
    }
</style>
