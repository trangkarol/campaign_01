<template>
<transition name="modal">
    <div class="modal v-modal-mask wrap-action" id="private-event" style="display: block" v-show="show">
        <div class="modal-dialog ui-block window-popup event-private-public private-event">
            <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close" @click.prevent="closeModal">
                <svg class="olymp-close-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-close-icon"></use></svg>
            </a>
            <article class="hentry post has-post-thumbnail thumb-full-width private-event">
                <div class="private-event-head inline-items">
                    <img src="/images/avatar77-sm.jpg" alt="author">
                    <div class="author-date">
                        <a class="h3 event-title" href="#">{{ shorten(campaign.title, false, 55) }}</a>
                        <div class="event__date">
                            <time class="published" datetime="2017-03-24T18:18">
                                #{{ campaign.hashtag }}
                            </time>
                        </div>
                    </div>
                </div>
                <div class="post-thumb">
                    <img :src="campaign.campaign_images.image_default" alt="photo">
                </div>
                <div class="row">
                    <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12">
                        <div class="post__author author vcard inline-items">
                            <img :src="campaign.owner[0] ? campaign.owner[0].image_thumbnail : null" alt="author">
                            <div class="author-date">
                                <a class="h6 post__author-name fn" href="#">{{ campaign.owner[0] ? campaign.owner[0].name : null }}</a>
                                {{ $t('campaigns.statistic.created_campaign') }}
                                <div class="post__date">
                                    <time class="published" datetime="2017-03-24T18:18">
                                        {{ $t('campaigns.statistic.at') }}
                                        {{ campaign.created_at }}
                                    </time>
                                </div>
                            </div>
                        </div>
                        <div>
                            {{ shorten(campaign.description, true, 300) }}
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                        <div class="event-description">
                            <h6 class="event-description-title">
                                {{ campaign.status.value == 0
                                    ? $t('campaigns.statistic.private_campaign')
                                    : $t('campaigns.statistic.public_campaign') }}
                            </h6>
                            <div class="place inline-items">
                                <span><svg class="olymp-add-a-place-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-add-a-place-icon"></use></svg>
                                {{ campaign.address }}</span>
                            </div>
                            <div class="place inline-items">
                                <span>
                                    <svg class="olymp-small-calendar-icon">
                                        <use xlink:href="/frontend/icons/icons.svg#olymp-small-calendar-icon"></use>
                                    </svg>
                                    {{ campaign.start_day.value | localeDate }}
                                    <template v-if="campaign.end_day">
                                        - {{ campaign.end_day.value | localeDate }}
                                    </template>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="post-additional-info inline-items">
                    <a class="post-add-icon inline-items place">
                        <svg class="olymp-heart-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use></svg>
                        <span>{{ campaign.number_of_likes }}</span>
                    </a>
                    <a class="post-add-icon inline-items place">
                        <svg class="olymp-speech-balloon-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-month-calendar-icon"></use></svg>
                        <span>{{ campaign.event_count }} events</span>
                    </a>
                    <div class="float-right">
                        <ul class="friends-harmonic">
                            <li v-for="(user, index) in campaign.user" v-if="index < 5">
                                <a><img :src="user.image_thumbnail" alt="friend"></a>
                            </li>
                            <li class="with-text">
                                {{ campaign.user.length }}
                                {{ $t('campaigns.statistic.user_join_campaign') }}
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="control-block-button post-control-button">
                    <a href="#" class="btn btn-control" @click.prevent="reload">
                        <svg class="olymp-like-post-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-weather-refresh-icon"></use></svg>
                    </a>
                    <a href="#" class="btn btn-control" @click.prevent="print">
                        <svg class="olymp-comments-post-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-blog-icon"></use></svg>
                    </a>
                </div>
            </article>
            <ul class="comments-list">
                <li>
                    <h3 class="date-title pull-left">{{ $t('campaigns.list-members') }}</h3>
                    <form class="w-search pull-right" @submit.prevent>
                        <div class="form-group with-button">
                            <input class="form-control" type="text" :placeholder="$t('messages.search-list-member')" v-model="search">
                            <button>
                                <svg class="olymp-magnifying-glass-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-magnifying-glass-icon"></use></svg>
                            </button>
                        </div>
                    </form>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th></th>
                                <th>{{ $t('form.label.full_name') }}</th>
                                <th>{{ $t('form.role') }}</th>
                                <th>{{ $t('campaigns.statistic.join_at') }}</th>
                                <th>{{ $t('campaigns.statistic.donation_times') }}</th>
                                <th>{{ $t('campaigns.statistic.more') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-if="!filteredUsers.members.length">
                                <td colspan="7" class="text-center">{{ $t('user.friend.nothing_found') }}</td>
                            </tr>
                            <tr v-for="(user, index) in filteredUsers.members" v-else>
                                <th scope="row">{{ index + 1 }}</th>
                                <td><span class="author-thumb"><img style="max-width: 36px" :src="user.image_thumbnail"></span></td>
                                <td v-if="user.pivot.role_id != 6">{{ user.name }}</td>
                                <td v-else><s>{{ user.name }}</s></td>
                                <td>{{ getRole(user) }}</td>
                                <td>{{ user.pivot.created_at | localeDate }}</td>
                                <td>{{ user.donations.length }}</td>
                                <td><a href="#" @click.prevent="selectUser(user)"><i class="fa fa-eye"></i></a></td>
                            </tr>
                        </tbody>
                    </table>
                </li>
                <li>
                    <h3 class="date-title">{{ $t('campaigns.statistic.guest') }}</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th></th>
                                <th>{{ $t('form.label.full_name') }}</th>
                                <th>{{ $t('campaigns.email') }}</th>
                                <th>{{ $t('campaigns.statistic.donation_times') }}</th>
                                <th>{{ $t('campaigns.statistic.more') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-if="!filteredUsers.guests.length">
                                <td colspan="6" class="text-center">{{ $t('user.friend.nothing_found') }}</td>
                            </tr>
                            <tr v-for="(donation, index) in filteredUsers.guests" v-else>
                                <th scope="row">{{ index + 1 }}</th>
                                <th><span class="author-thumb"><img style="max-width: 36px" :src="donation[0].user.image_thumbnail"></span></th>
                                <td>{{ donation[0].user.name }}</td>
                                <td>{{ donation[0].user.email }}</td>
                                <td>{{ donation.length }}</td>
                                <td><a href="#" @click.prevent="selectUser(donation, true)"><i class="fa fa-eye"></i></a></td>
                            </tr>
                        </tbody>
                    </table>
                </li>
            </ul>
            <div class="comment-form inline-items">
                <div class="with-icon-right is-empty float-right">
                    {{ $t('campaigns.statistic.reported_at') }}
                    <strong>{{ data.reported_at }}</strong>
                    (<a href="#" @click.prevent="reload">{{ $t('campaigns.statistic.recreate') }}</a>)
                </div>
            </div>
        </div>
        <modal :show.sync="showModal" v-if="selectedUser.id">
            <h4 slot="header">{{ selectedUser.name }} </h4>
            <div slot="main">
                <strong>{{ $t('campaigns.statistic.info', [selectedUser.name, selectedUser.donations.length, Object.keys(selectedUser.events).length]) }}</strong>
                <table class="table table-striped" style="margin-top: 1em">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ $t('form.label.goal') }}</th>
                            <th>{{ $t('events.donation.donate') }}</th>
                            <th class="text-capitalize">{{ $t('actions.at') }}</th>
                            <th>{{ $t('campaigns.statistic.percent') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <td colspan="5" class="text-center table-info" v-if="!Object.keys(selectedUser.events).length">{{ $t('user.friend.nothing_found') }}</td>
                        <template v-for="(donations, index) in selectedUser.events" v-else>
                            <td colspan="5" class="text-center table-success"><i class="fa fa-calendar"></i> {{ index }}</td>
                            <tr v-for="(donation, index) in donations">
                                <th scope="row">{{ index + 1 }}</th>
                                <td>{{ donation.goal.goal + ' ' + donation.goal.donation_type.quality.name + ' ' + donation.goal.donation_type.name }}</td>
                                <td>{{ donation.value  + ' ' + donation.goal.donation_type.quality.name }}</td>
                                <td>{{ donation.donated_at }}</td>
                                <td>{{ Math.round(donation.value / donation.goal.goal * 100) }}%</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </modal>
    </div>
</transition>
</template>
<script>
import { mapState } from 'vuex'
import string from '../../helpers/mixin/string'
import Modal from '../libs/Modal'

export default {
    mixins: [string],
    props: ['show', 'data'],
    data() {
        return {
            settings: window.Laravel.settings,
            search: '',
            showModal: false,
            selectedUser: {},
            roles: {
                3: this.$t('campaigns.roles.owner'),
                4: this.$t('campaigns.roles.moderator'),
                5: this.$t('campaigns.roles.member'),
                6: this.$t('campaigns.roles.blocked'),
            }
        }
    },
    computed: {
        ...mapState('campaign', ['campaign']),
        filteredUsers() {
            return {
                members: this.data.users ? this.data.users.filter((user) => {
                        return user.name.toLowerCase().trim().indexOf(this.search.toLowerCase().trim()) >= 0 ||
                            (user.email && user.email.toLowerCase().trim().indexOf(this.search.toLowerCase().trim()) >= 0)
                    }) : [],
                guests: this.data.users ? Object.values(this.data.guests).filter((user) => {
                    return user[0].user.name.toLowerCase().trim().indexOf(this.search.toLowerCase().trim()) >= 0 ||
                        (user.email && user[0].user.email.toLowerCase().trim().indexOf(this.search.toLowerCase().trim()) >= 0)
                    }) : []
            }
        }
    },
    methods: {
        closeModal() {
            this.$emit('update:show', false)
        },
        reload() {
            this.$emit('reload')
        },
        print() {
            window.print()
        },
        getRole(user) {
            return this.roles[user.pivot.role_id]
        },
        selectUser(user, isGuest = false) {
            this.showModal = true

            if (!isGuest) {
                this.selectedUser = user
            } else {
                this.selectedUser = user[0].user
                this.$set(this.selectedUser, 'donations', user)
            }

            const events = _.groupBy(this.selectedUser.donations, 'event.title')
            this.$set(this.selectedUser, 'events', events)
        }
    },
    filters: {
        localeDate(date) {
            return date ? new Date(date).toLocaleDateString(window.Laravel.locale) : null
        }
    },
    mounted() {
        document.addEventListener("keydown", (e) => {
            if (this.show && e.keyCode == 27) {
                this.closeModal()
            }
        })
    },
    components: {
        Modal
    }
}
</script>

<style lang="scss" scoped>
    .v-modal-mask {
      position: fixed;
      z-index: 9998;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, .5);
      display: table;
      transition: opacity .3s ease;
    }
    .modal-dialog {
        overflow-y: initial !important;
    }
    .tab-content {
        max-height: 50vh;
        overflow-y: auto;
    }
    .ui-block-content, .modal-footer {
        padding-bottom: 0;
    }
    .wrap-action {
        overflow-y: scroll;
        &::-webkit-scrollbar {
            display: none;
        }
    }
    body.modal-dialog {
        overflow: hidden;
        position: fixed;
    }
    .comments-list {
        li {
            padding: 20px;
        }
        .post__author {
            img {
                width: 36px;
                height: 36px;
            }
        }
    }
    .date-title {
        font-weight: 300;
        line-height: 1;
    }
    .friends-harmonic {
        li {
            a {
                img {
                    max-width: 30px;
                }
            }
        }
    }
    .place {
        font-size: 13px;
        svg {
            fill: #c10d4a;
        }
    }
    .w-search {
        input {
            padding: 10px 15px;
            margin-bottom: 5px;
        }
    }
    .table {
        td, th {
            padding: 0.4rem;
        }
    }
</style>
