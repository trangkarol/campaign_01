<template>
<transition name="modal">
    <div class="modal v-modal-mask wrap-action" id="private-event" style="display: block">
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
                <div class="container">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row table-header">
                                <div class="col-md-1">
                                    <a class="place inline-items post-add-icon">
                                        <svg class="olymp-small-calendar-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-small-calendar-icon"></use>
                                        </svg>
                                    </a>
                                </div>
                                <div class="col-md-11">
                                    <div class="row">
                                        <div class="col-md-2">{{ $t('campaigns.statistic.donate') }}</div>
                                        <div class="col-md-2">{{ $t('campaigns.statistic.received') }}</div>
                                        <div class="col-md-1">{{ $t('campaigns.statistic.spent') }}</div>
                                        <div class="col-md-1">{{ $t('campaigns.statistic.remain') }}</div>
                                        <div class="col-md-6">{{ $t('campaigns.statistic.note') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row" v-for="month in getMonth()">
                                <div class="col-md-1 month-statistic">{{month}}</div>
                                <div class="col-md-11">
                                    <div class="row hover-change" v-for="(donate, donationTypeId) in data.donations[month]">
                                        <div class="col-md-2 statistic-padding">
                                            {{ totalOfReceived(donate).name }}
                                        </div>
                                        <div class="col-md-2 statistic-padding">
                                            {{ totalOfReceived(donate).total }}
                                        </div>
                                        <div class="col-md-1 statistic-padding">
                                            {{ totalOfSpent(month, donationTypeId).total }}
                                        </div>
                                        <div class="col-md-1 statistic-padding">
                                            {{
                                                totalOfReceived(donate).total - totalOfSpent(month, donationTypeId).total
                                            }}
                                            {{
                                                totalRemain(totalOfReceived(donate).name, totalOfReceived(donate).total - totalOfSpent(month, donationTypeId, true).total)
                                            }}
                                        </div>
                                        <div class="col-md-6 note">
                                            <i class="fa fa-commenting-o" aria-hidden="true"></i>
                                            <span><input class="input-sm"/></span>
                                        </div>
                                    </div>
                                    <div class="row hover-change" v-for="(expense, donationTypeId) in data.expenses[month]">
                                        <div class="col-md-2 statistic-padding">
                                            {{ totalOfSpent(month, donationTypeId).name }}
                                        </div>
                                        <div class="col-md-2 statistic-padding">
                                            {{ 0 }}
                                        </div>
                                        <div class="col-md-1 statistic-padding">
                                            {{ totalOfSpent(month, donationTypeId).total }}
                                        </div>
                                        <div class="col-md-1 statistic-padding">
                                            {{
                                                0 - totalOfSpent(month, donationTypeId).total
                                            }}
                                            {{
                                                totalRemain(totalOfSpent(month, donationTypeId).name,
                                                0 - totalOfSpent(month, donationTypeId, true).total)
                                            }}
                                        </div>
                                        <div class="col-md-6 note">
                                            <i class="fa fa-commenting-o" aria-hidden="true"></i>
                                            <input  class="input-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row table-header">{{ $t('campaigns.statistic.total') }}</div>
                            <div class="row">
                                <div class="col-md-1 month-statistic"></div>
                                <div class="col-md-11">
                                    <div class="row hover-change" v-for="(value, name) in total">
                                        <div class="col-md-2 statistic-padding">
                                            {{ name }}
                                        </div>
                                        <div class="col-md-2 statistic-padding">
                                            {{ value }}
                                        </div>
                                        <div class="col-md-8 note">
                                            <i class="fa fa-commenting-o" aria-hidden="true"></i>
                                            <span><input class="input-sm"/></span>
                                        </div>
                                    </div>
                                </div>
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
            <div v-if="data.event_count">
                <ul class="comments-list" v-for="(events, day) in data.events">
                    <li><h3 class="date-title">{{ day | localeDate}}</h3></li>
                    <li v-for="event in events">
                        <div class="post__author author vcard inline-items">
                            <img :src="event.media[0].image_thumbnail" alt="author" v-if="event.media.length">
                            <div class="author-date">
                                <router-link class="h6 post__author-name fn"
                                    :to="{
                                        name: 'event.index',
                                        params: { slug: $route.params.slug, slugEvent: event.slug }
                                    }">
                                    {{ event.title }}
                                </router-link>
                                <div class="post__date">
                                    <time class="published" datetime="2017-03-24T18:18">
                                        {{ $t('campaigns.statistic.created_at') }}
                                        {{ event.created_at | localeDate }}
                                    </time>
                                </div>
                            </div>
                            <a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
                        </div>
                        <div>
                            <a class="place inline-items post-add-icon">
                                <svg class="olymp-add-a-place-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-add-a-place-icon"></use></svg>
                                <span>{{ event.address }}</span>
                            </a>
                            <a class="place inline-items post-add-icon">
                                <svg class="olymp-small-calendar-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-small-calendar-icon"></use></svg>
                                <span>{{ getDay(event.settings, settings.events.start_day) | localeDate }}</span>
                                <span v-if="getDay(event.settings, settings.events.end_day)">
                                    - {{ getDay(event.settings, settings.events.end_day) | localeDate }}
                                </span>
                            </a>
                            <a class="post-add-icon inline-items place">
                                <svg class="olymp-heart-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-heart-icon"></use></svg>
                                <span>{{ event.number_of_likes }}</span>
                            </a>
                        </div>
                        <div>
                            <table class="mt-3 table-bordered table table-sm" v-if="event.goals.length">
                                <thead class="thead-default">
                                    <tr>
                                        <th><a class="place inline-items post-add-icon">
                                            <svg class="olymp-add-a-place-icon">
                                                <use xlink:href="/frontend/icons/icons.svg#olymp-star-icon"></use>
                                            </svg>
                                        </a></th>
                                        <th>{{ $t('events.donation.donate') }}</th>
                                        <th>{{ $t('events.donation.goal') }}</th>
                                        <th>{{ $t('events.donation.received') }}</th>
                                        <th>{{ $t('campaigns.statistic.donation_times') }}</th>
                                        <th>{{ $t('events.expenses_statistic.spent') }}</th>
                                        <th>{{ $t('campaigns.statistic.spent_times') }}</th>
                                        <th class="text-capitalize">{{ $t('events.expenses_statistic.remain') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(goal, i) in event.goals">
                                        <th scope="row">{{ i + 1 }}</th>
                                        <td>{{ goal.donation_type.name + ' (' + goal.donation_type.quality.name + ')' }}</td>
                                        <td>{{ goal.goal }}</td>
                                        <td>
                                            {{ sumValue(goal.donations, 'value') }}
                                            ({{ getPercent(sumValue(goal.donations, 'value'), goal.goal) }} %)
                                        </td>
                                        <td>{{ goal.donations.length }}</td>
                                        <td>
                                            {{ sumValue(goal.expenses, 'cost') }}
                                            ({{ getPercent(sumValue(goal.expenses, 'cost'), sumValue(goal.donations, 'value')) }}%)
                                        </td>
                                        <td>{{ goal.expenses.length }}</td>
                                        <td>
                                            {{ sumValue(goal.donations, 'value') - sumValue(goal.expenses, 'cost') }}
                                            ({{ 100 - getPercent(sumValue(goal.expenses, 'cost'), sumValue(goal.donations, 'value')) }}%)
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </li>
                </ul>
            </div>
            <div v-else>
                <p class="h6 post__author-name fn text-center align-middle mt-1">
                    {{ $t('campaigns.statistic.no_event') }}
                </p>
            </div>
            <div class="comment-form inline-items">
                <div class="with-icon-right is-empty float-right">
                    {{ $t('campaigns.statistic.reported_at') }}
                    <strong>{{ data.reported_at }}</strong>
                    (<a href="#" @click.prevent="reload">{{ $t('campaigns.statistic.recreate') }}</a>)
                </div>
            </div>
        </div>
    </div>
</transition>
</template>
<script>
import { mapState } from 'vuex'
import string from '../../helpers/mixin/string'
export default {
    mixins: [string],
    props: ['show', 'data'],
    data() {
        return {
            settings: window.Laravel.settings,
            total: {}
        }
    },
    computed: mapState('campaign', ['campaign']),
    methods: {
        closeModal() {
            this.$emit('update:show', false)
        },
        reload() {
            this.total = {}
            this.$emit('reload')
        },
        getDay(settings, key) {
            let filter = settings.filter(setting => {
                return setting.key == key
            })[0]

            return filter ? filter.value : null
        },
        sumValue(object, key) {
            return object.reduce((sum, value) => sum + value[key], 0)
        },
        getPercent(divisor, devide) {
            if (devide == 0)
                return null
            return Math.round(divisor/devide*100)
        },
        print() {
            window.print()
        },

        totalOfReceived(donates) {
            let result = {}
            result.name = `${donates[0].goal.donation_type.name}(${donates[0].goal.donation_type.quality.name})`
            result.total = _.sum(donates.map(donate => donate.value ))
            return result
        },

        totalOfSpent(month, key, isdelete=false) {
            let result = {}
            let expenses = this.data.expenses[month][key]
            if (expenses) {
                result.total = _.sum(this.data.expenses[month][key].map(expense => expense.cost ))
                result.name = `${expenses[0].goal.donation_type.name}(${expenses[0].goal.donation_type.quality.name})`
                if (isdelete) {
                    delete this.data.expenses[month][key]
                }
            } else {
                result.total = 0
                result.name = ''
            }
            return result
        },

        getMonth() {
            return _.uniq([..._.keys(this.data.donations), ..._.keys(this.data.expenses)])
        },

        totalRemain(name, remain) {
            if (this.total[name]) {
                this.total[name] += remain
            } else {
                this.total[name] = remain
            }
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
        width: 1025px !important;
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

    .private-event {
        .month-statistic {
            font-weight: bold;
            color: #474b4c;
            padding: 5px 0px;
        }
        .table-header {
            background: #eceff0;
            padding: 5px 0px;
            font-weight: bold;
            color: #474b4c;
        }

        .hover-change {
            border-top: 1px solid #eceff0;
            .statistic-padding {
                padding-top: 7px;
            }
            &:hover {
                background-color: #eceff0;
            }
            .note {
                input {
                    border: 0px !important;
                    display: inline;
                    width: 95%;
                }
            }
        }
    }
</style>
