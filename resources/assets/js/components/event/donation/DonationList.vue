<template>
<div>
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="ui-block">
                <div class="birthday-item inline-items badges" v-for="(goal, i) in event.complete_percent">
                    <div class="author-thumb">
                        <router-link :to="{ name: 'donation.received', params: { id: goal.id }}" class="h6 author-name">
                            <img src="/images/badge3.png" alt="author">
                            <div class="label-avatar bg-primary">
                                {{ goal.donations.filter(d => d.status == 1).length }}
                            </div>
                        </router-link>
                    </div>
                    <div class="birthday-author-name">
                        <router-link :to="{ name: 'donation.received', params: { id: goal.id }}" class="h6 author-name">
                            {{ goal.donation_type.name }} ({{ Math.round(donateInfo[i]/goal.goal*100) }}%)
                        </router-link>
                        <div class="birthday-date">
                            {{ $t('events.donation.receive')
                                + ' ' + donateInfo[i]
                                + '/' + goal.goal
                                + ' ' + goal.donation_type.quality.name
                                + ' ' + goal.donation_type.name
                                + ' ' + $t('events.donation.with')
                                + ' ' + goal.donations.filter(d => d.status == 1).length
                                + ' ' + $t('events.donation.donations') }}
                        </div>
                    </div>
                    <div class="skills-item">
                        <div class="skills-item-meter">
                            <span class="skills-item-meter-active"
                                :style="{ width: (donateInfo[i]/goal.goal*100 > 100 ? 100 : donateInfo[i]/goal.goal*100) + '%' }">
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ui-block responsive-flex">
                <div class="ui-block-title">
                    <div class="w-select">
                        <div class="title">{{ $t('events.donation.donation_status') }}</div>
                        <fieldset class="form-group">
                            <select ref="select" class="selectpicker form-control" size="auto" v-model="params.status">
                                <option value="">{{ $t('events.expenses_statistic.all') }}</option>
                                <option :value="true">{{ $t('events.donation.confirmed') }}</option>
                                <option :value="false">{{ $t('events.donation.unconfimred') }}</option>
                            </select>
                        </fieldset>
                    </div>
                    <div class="w-select">
                        <div class="title">{{ $t('events.donation.goal') }}</div>
                        <fieldset class="form-group">
                            <select ref="select" class="selectpicker form-control" size="auto" v-model="params.goal_id">
                                <option value="">{{ $t('events.expenses_statistic.all') }}</option>
                                <option :value="goal.id" v-for="goal in dataGoals">{{ goal.donation_type.name }}</option>
                            </select>
                        </fieldset>
                    </div>
                    <div class="w-select">
                        <div class="title">{{ $t('messages.members') }}</div>
                        <fieldset class="form-group">
                            <select ref="select" class="selectpicker form-control" size="auto" v-model="params.user_id">
                                <option value="">{{ $t('events.expenses_statistic.all') }}</option>
                                <option :value="user.id" v-for="user in campaign.user">{{ user.name }}</option>
                            </select>
                        </fieldset>
                    </div>

                    <form class="w-search" @submit.prevent>
                        <div class="form-group with-button">
                            <input class="form-control" type="text" :placeholder="$t('events.search') + '...'" v-model="params.searchKey">
                            <button>
                                <svg class="olymp-magnifying-glass-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-magnifying-glass-icon"></use></svg>
                            </button>
                        </div>
                    </form>
                    <div class="more">
                        <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                        <ul class="more-dropdown">
                            <li>
                                <a href="#" @click.prevent="clearFilter">{{ $t('events.donation.clear_filter') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <list :data="donations">
            </list>
            <div class="text-center">
                <button type="button" class="btn btn-primary" @click.prevent="getDonations" v-if="hasData">{{ $t('user.sidebar.load_more') }} <i class="fa fa-spinner" v-show="loading"></i></button>
                <button type="button" class="btn btn-primary" v-else>{{ $t('user.quote.no_data') }}</button>
            </div>
        </div>
    </div>
</div>
</template>

<script>
    import { mapActions, mapState } from 'vuex'
    import List from './List'
    import { get } from '../../../helpers/api.js'

    export default {
        data() {
            return {
                pageType: 'event',
                donations: [],
                params: {
                    'status': '',
                    'goal_id': '',
                    'user_id': '',
                    'searchKey': ''
                },
                page: 0,
                loading: false,
                hasData: true
            }
        },
        methods: {
            progresbar() {
                let $progress_bar = $('.skills-item')
                $progress_bar.appear({force_process: true});
                $progress_bar.on('appear', function () {
                    let current_bar = $(this);
                    if (!current_bar.data('inited')) {
                        current_bar.find('.skills-item-meter-active').fadeTo(300, 1).addClass('skills-animate');
                        current_bar.data('inited', true);
                    }
                });
            },
            getDonations() {
                if (this.hasData) {
                    this.page++
                    this.loading = 1
                    get(`event/${this.pageId}/list-donations?status=${this.params.status}&goal_id=${this.params.goal_id}&user_id=${this.params.user_id}&searchKey=${this.params.searchKey}&page=${this.page}`)
                        .then((res) => {
                            this.donations = [...this.donations, ...res.data.donations.data]
                            this.hasData = !!res.data.donations.next_page_url
                            this.loading = false
                        })
                        .catch(err => {})
                }
            },
            clearFilter() {
                $('.selectpicker').selectpicker('val', '')
                this.params = {
                    'status': '',
                    'goal_id': '',
                    'user_id': '',
                    'searchKey': ''
                }
            }
        },
        computed: {
            ...mapState('event', ['event', 'dataGoals']),
            ...mapState('campaign', ['campaign']),
            donateInfo() {
                let donated = []
                this.event.complete_percent.forEach((value, index) => {
                    donated[index] = value.donations.filter(donation => donation.status == 1).reduce((sum, value) => sum + value.value, 0)
                })
                return donated
            },
        },
        watch: {
            params: {
                handler(newParams) {
                    this.hasData = true
                    this.page = 0
                    this.donations = []
                    this.getDonations()
                },
                deep: true
            }
        },
        created() {
            this.getDonations()
        },
        mounted() {
            this.progresbar()
            $('.selectpicker').selectpicker()
        },
        components: {
            List
        }
    }
</script>
