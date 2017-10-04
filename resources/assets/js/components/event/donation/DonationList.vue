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
        </div>
    </div>
</div>
</template>

<script>
    import { mapActions, mapState } from 'vuex'

    export default {
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
            }
        },
        computed: {
            ...mapState('event', ['event']),
            donateInfo() {
                let donated = []
                this.event.complete_percent.forEach((value, index) => {
                    donated[index] = value.donations.filter(donation => donation.status == 1).reduce((sum, value) => sum + value.value, 0)
                })
                return donated
            },
        },
        mounted() {
            this.progresbar()
        },
    }
</script>
