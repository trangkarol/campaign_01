<template>
    <div>
        <div class="row">
            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <div class="ui-block">
                    <div class="ui-block-content">
                        <div class="monthly-indicator">
                            <a href="#" class="btn btn-control bg-breez">
                                <svg class="olymp-stats-arrow">
                                    <use xlink:href="/frontend/icons/icons.svg#olymp-stats-arrow"></use>
                                </svg>
                            </a>
                            <div class="monthly-count">
                                {{ $t('events.donation.goal')
                                    + ': ' + info.goal
                                    + " " + info.quantity
                                    + " " + info.type
                                }}
                                <span class="period">{{ $t('events.donation.goal_des') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <div class="ui-block">
                    <div class="ui-block-content">
                        <div class="monthly-indicator">
                            <a href="#" class="btn btn-control bg-not-accepted">
                                <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                            </a>
                            <div class="monthly-count">
                                {{ $t('events.donation.waiting')
                                    + ' : ' + infoNotAccepted.notReceived
                                    + " " + infoNotAccepted.quantity
                                    + " " + infoNotAccepted.type
                                }}
                                <span class="indicator negative">{{ Math.round(infoNotAccepted.percent) }}%</span>
                                <span class="period">
                                    {{ $t('events.donation.with')
                                        + " " + infoNotAccepted.count
                                        + " " + $t('events.donation.donations')
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <div class="ui-block">
                    <div class="ui-block-content">
                        <div class="monthly-indicator">
                            <a href="#" class="btn btn-control bg-primary negative">
                                <svg class="olymp-stats-arrow">
                                    <use xlink:href="/frontend/icons/icons.svg#olymp-stats-arrow"></use>
                                </svg>
                            </a>
                            <div class="monthly-count">
                                {{ $t('events.donation.received')
                                    + ': ' + info.received
                                    + " " + info.quantity
                                    + " " + info.type
                                }}
                                <span class="indicator negative">{{ Math.round(info.percent) }}%</span>
                                <span class="period">
                                    {{ $t('events.donation.with')
                                        + " " + info.count
                                        + " " + $t('events.donation.donations')
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="progress">
                    <div
                        class="progress-bar progress-bar-striped active bg-primary"
                        role="progressbar"
                        :style="{
                            width: (info.percent > 100 ?
                                info.goal / info.received :
                                info.received / info.goal) * 100 + '%'
                        }"
                        :aria-valuenow="info.percent"
                        aria-valuemin="0"
                        :aria-valuemax="info.percent > 100 ? Math.round(info.percent) : 100">
                        {{  info.percent > 100 ? 100 : Math.round(info.percent) + '% '
                            + $t('events.donation.received') }}
                    </div>
                    <div
                        class="progress-bar progress-bar-striped bg-success active"
                        role="progressbar"
                        :style="{ width: 100 * (1 - info.goal / info.received) + '%'}"
                        v-show="info.percent > 100">
                        {{ Math.round(info.percent - 100) + '% ' + $t('events.donation.over') }}
                    </div>
                </div>
                <div class="progress">
                    <div
                        class="progress-bar progress-bar-striped active bg-not-accepted"
                        role="progressbar"
                        :style="{
                            width: (infoNotAccepted.percent > 100 ?
                                infoNotAccepted.goal / infoNotAccepted.notReceived :
                                infoNotAccepted.notReceived / infoNotAccepted.goal) * 100 + '%'
                        }"
                        :aria-valuenow="infoNotAccepted.percent"
                        aria-valuemin="0"
                        :aria-valuemax="infoNotAccepted.percent > 100 ? Math.round(infoNotAccepted.percent) : 100">
                        {{ infoNotAccepted.percent > 100 ? 100 : Math.round(infoNotAccepted.percent) + '% '
                            + $t('events.donation.waiting_accept') }}
                    </div>
                    <div
                        class="progress-bar progress-bar-striped bg-success active"
                        role="progressbar"
                        :style="{ width: 100 * (1 - infoNotAccepted.goal / infoNotAccepted.notReceived )  + '%'}"
                        v-show="infoNotAccepted.percent > 100">
                        {{ Math.round(infoNotAccepted.percent - 100) + '% ' + $t('events.donation.over') }}
                    </div>
                </div>
            </div>
            <div class="col-md-12" v-show="event.manage">
                <ul class="cat-list-bg-style align-center sorting-menu">
                    <router-link
                        :to="{ name: 'donation.received' }"
                        class="cat-list__item"
                        exact-active-class="active"
                        tag="li">
                        <a href="#">{{ $t('events.donation.list') }}</a>
                    </router-link>
                    <router-link
                        :to="{ name: 'donation.manage' }"
                        class="cat-list__item"
                        exact-active-class="active"
                        tag="li">
                        <a href="#">{{ $t('events.donation.manage') }}</a>
                    </router-link>
                </ul>
            </div>
            <router-view/>
        </div>
    </div>
</template>
<script>
    import { mapActions, mapState } from 'vuex'
    import { get } from '../../../helpers/api'
    export default {
        computed: {
            ...mapState('event', ['event']),
            info() {
                const donate = this.event.complete_percent.filter(goal => goal.id == this.$route.params.id)[0]
                const receive = donate.donations.filter(donation => donation.status == 1)
                return {
                    goal: donate.goal,
                    received: receive.reduce((sum, value) => sum + value.value, 0),
                    type: donate.donation_type.name,
                    quantity: donate.donation_type.quality.name,
                    percent: receive.reduce((sum, value) => sum + value.value, 0) / donate.goal * 100,
                    count: receive.length
                }
            },

            infoNotAccepted() {
                const donate = this.event.complete_percent.filter(goal => goal.id == this.$route.params.id)[0]
                const notAccepted = donate.donations.filter(donation => donation.status == 0)

                return {
                    goal: donate.goal,
                    notReceived: notAccepted.reduce((sum, value) => sum + value.value, 0),
                    type: donate.donation_type.name,
                    quantity: donate.donation_type.quality.name,
                    percent: notAccepted.reduce((sum, value) => sum + value.value, 0) / donate.goal * 100,
                    count: notAccepted.length
                }
            }
        }
    }
</script>
<style lang="scss" scoped>
    .notification-list {
        min-height: 0;
    }
    .cat-list-bg-style {
        margin: 20px;
    }
    .bg-not-accepted {
        background-color: #38a9ff;
    }
</style>
