<template>
    <div>
        <div class="row">
            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                <div class="ui-block">
                    <div class="ui-block-content">
                        <div class="monthly-indicator">
                            <a href="#" class="btn btn-control bg-breez" @click.prevent="showUserStatistic">
                                <svg class="olymp-happy-faces-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-happy-faces-icon"></use></svg>
                            </a>
                            <div class="monthly-count">
                                {{ users.count + ' ' + $t('campaigns.statistic.members') }}
                                <span class="indicator positive"> + {{ users.today_count + ' ' + $t('campaigns.statistic.today') }}</span>
                                <span class="period text-capitalize">{{ $t('campaigns.statistic.members') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                <div class="ui-block">
                    <div class="ui-block-content">
                        <div class="monthly-indicator">
                            <a href="#" class="btn btn-control bg-primary negative" @click.prevent>
                                <svg class="olymp-stats-arrows"><use xlink:href="/frontend/icons/icons.svg#olymp-month-calendar-icon"></use></svg>
                            </a>
                            <div class="monthly-count">
                                {{ events.count + ' ' + $t('campaigns.statistic.events') }}
                                <span class="indicator negative"> {{ events.upcoming + ' ' + $t('campaigns.statistic.upcomming') }}</span>
                                <span class="period">
                                    {{ events.ongoing + ' ' + $t('campaigns.statistic.going_on') + ' ' + $t('campaigns.statistic.and') + ' ' + events.finished + ' ' + $t('campaigns.statistic.finished')}}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                <div class="ui-block">
                    <div class="ui-block-content">
                        <div class="monthly-indicator">
                            <a href="#" class="btn btn-control bg-yellow negative" @click.prevent>
                                <svg class="olymp-stats-arrows"><use xlink:href="/frontend/icons/icons.svg#olymp-month-calendar-icon"></use></svg>
                            </a>
                            <div class="monthly-count">
                                {{ actions.count + ' ' + $t('campaigns.statistic.actions')  }}
                                 <span class="indicator negative"> +{{ actions.today_count +  ' ' + $t('campaigns.statistic.today') }}</span>
                                <span class="period text-capitalize">{{ $t('campaigns.statistic.actions') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                <div class="ui-block">
                    <div class="ui-block-content">
                        <div class="monthly-indicator">
                            <a href="#" class="btn btn-control bg-purple negative" @click.prevent="getStatisticInfo">
                                <svg class="olymp-stats-arrows"><use xlink:href="/frontend/icons/icons.svg#olymp-stats-icon"></use></svg>
                            </a>
                            <div class="monthly-count">
                                {{ $t('campaigns.statistic.statistic') }}
                                <span class="period text-capitalize">{{ $t('campaigns.statistic.full_statistic') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <information :show.sync="showInfo" :data="information" @reload="fetchStatisticInfo" v-if="showInfo"/>
        <user-statistic :show.sync="showUser" :data="userInfo" @reload="fetchUserStatistic"/>

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" v-show="users.chart_label.length > 1">
                <div class="ui-block responsive-flex" data-mh="pie-chart">
                    <div class="ui-block-title">
                        <div class="h6 title text-capitalize">{{ $t('campaigns.statistic.members') }}</div>
                    </div>

                    <div class="ui-block-content">
                        <div class="chart-js chart-js-line-graphic">
                            <line-chart
                                id="line-graphic-chart"
                                :chart-data="userChart"
                                :options="chartOptions"
                                :width="730"
                                :height="100">
                            </line-chart>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" v-show="events.chart_label.length > 1">
                <div class="ui-block responsive-flex" data-mh="pie-chart">
                    <div class="ui-block-title">
                        <div class="h6 title text-capitalize">{{ $t('campaigns.statistic.events') }}</div>
                    </div>
                    <div class="ui-block-content">
                        <div class="chart-js chart-js-line-graphic">
                            <line-chart
                                id="line-graphic-chart"
                                :chart-data="eventChart"
                                :options="chartOptions"
                                :width="730"
                                :height="100">
                            </line-chart>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" v-show="actions.chart_label.length > 1">
                <div class="ui-block responsive-flex" data-mh="pie-chart">
                    <div class="ui-block-title">
                        <div class="h6 title text-capitalize">{{ $t('campaigns.statistic.actions') }}</div>
                    </div>
                    <div class="ui-block-content">
                        <div class="chart-js chart-js-line-graphic">
                            <line-chart
                                id="line-graphic-chart"
                                :chart-data="actionChart"
                                :options="chartOptions"
                                :width="730" :height="300">
                            </line-chart>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-12" v-show="actions.chart_label.length <= 1 && events.chart_label.length <=1 && users.chart_label.length">
                <h4 class="text-center">{{ $t('campaigns.statistic.not_enought_data') }}</h4>
            </div>
        </div>
    </div>
</template>

<script>
import LineChart from '../libs/chart/LineChart.js'
import { get } from '../../helpers/api'
import Information from './ExportInformation'
import UserStatistic from './UserStatistic'

export default {
    data() {
        return {
            users: {
                chart_label: []
            },
            events: {
                chart_label: []
            },
            actions: {
                chart_label: []
            },
            userChart: {},
            eventChart: {},
            actionChart: {},
            chartOptions: {
                deferred: {
                    delay: 300
                },
                legend: {
                    display: false
                },
                responsive: true,
                scales: {
                    xAxes: [{
                        gridLines: {
                            color: "#f0f4f9"
                        },
                        ticks: {
                            fontColor: '#888da8'
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: true
                        },
                        ticks: {
                            beginAtZero:true,
                            fontColor: '#888da8'
                        }
                    }]
                }
            },
            showInfo: false,
            information: {},
            showUser: false,
            userInfo: {}
        }
    },
    methods: {
        fetchUserStatistic() {
            this.$Progress.start()
            get(`campaign/${this.pageId}/user-statistic`)
                .then(res => {
                    this.$set(this.userInfo, 'users', res.data.data.approved_users)
                    const guests = _.groupBy(res.data.data.donations, 'user.id')
                    this.$set(this.userInfo, 'guests', guests)
                    this.$set(this.userInfo, 'reported_at', new Date().toLocaleString(window.Laravel.locale))
                    this.$Progress.finish()
                })
                .catch(() => this.$Progress.fail)
        },
        showUserStatistic() {
            this.fetchUserStatistic()
            this.showUser = true
        },
        fetchStatisticInfo() {
            this.information = {}
            this.$Progress.start()
            get(`campaign/${this.pageId}/get-export-data`)
                .then(res => {
                    this.information = res.data.data
                    this.information.expenses = res.data.expenses
                    this.information.donations = res.data.donates
                    console.log(this.information)
                    this.information.event_count = this.information.events.length
                    this.information.events = _.groupBy(this.information.events, 'settings[0].date_string')
                    this.information.reported_at = new Date().toLocaleString(window.Laravel.locale)
                    this.$Progress.finish()
                })
                .catch(() => this.$Progress.fail)
        },
        getStatisticInfo() {
            this.fetchStatisticInfo()
            this.showInfo = true
        }
    },
    created() {
        get(`campaign/${this.pageId}/statistic`)
            .then(res => {
                this.users = res.data.data.users
                this.events = res.data.data.events
                this.actions = res.data.data.actions
                this.userChart = Object.assign({})
                this.$set(this.userChart, 'labels', res.data.data.users.chart_label)
                this.$set(this.userChart, 'datasets', [])
                this.userChart.datasets.push({
                    label: this.$t('events.expenses.cost'),
                    backgroundColor: "rgba(8,221,193,0.6)",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 4,
                    pointRadius: 0,
                    pointHoverRadius: 8,
                    data: res.data.data.users.chart_data
                })
                this.eventChart = Object.assign({})
                this.$set(this.eventChart, 'labels', res.data.data.events.chart_label)
                this.$set(this.eventChart, 'datasets', [])
                this.eventChart.datasets.push({
                    label: this.$t('events.expenses.cost'),
                    backgroundColor: "rgba(255,94,58,0.6)",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 4,
                    pointRadius: 0,
                    pointHoverRadius: 8,
                    data: res.data.data.events.chart_data
                })
                this.actionChart = Object.assign({})
                this.$set(this.actionChart, 'labels', res.data.data.actions.chart_label)
                this.$set(this.actionChart, 'datasets', [])
                this.actionChart.datasets.push({
                    label: this.$t('actions.expenses.cost'),
                    backgroundColor: "rgba(255,220,27,0.6)",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 4,
                    pointRadius: 0,
                    pointHoverRadius: 8,
                    data: res.data.data.actions.chart_data
                })
            })
    },
    components: {
        LineChart,
        Information,
        UserStatistic
    }
}
</script>
