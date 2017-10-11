<template>
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="ui-block">
                <div class="ui-block-title">
                    <h6 class="title">{{ $t('events.donation.list_support') }}</h6>
                </div>
                <ul class="notification-list">
                    <li v-for="donation in donations">
                        <div class="author-thumb">
                            <img :src="donation.user ? donation.user.image_thumbnail : '/images/faved-page8.jpg'" alt="author" style="max-height: 100%">
                        </div>
                        <div class="notification-event">
                            <router-link
                                :to="{name: 'user.timeline', params: { slug: donation.user ? donation.user.slug : null }}">
                                <a href="javascript:void(0)" class="h6 notification-friend">
                                    {{ donation.donor_name || donation.user.name }}
                                </a>
                            </router-link>
                            {{ $t('events.donation.donate')
                                + " " + donation.value
                                + " " + type.quantity
                                + " " + type.donate
                            }}.
                            <span class="notification-date">
                                <time class="entry-date updated">{{ donation.donated_at }}</time>
                            </span>
                        </div>
                        <span class="notification-icon accepted">
                            <i class="fa fa-check" aria-hidden="true"></i>
                        </span>
                    </li>
                    <li v-show="!donations.length">{{ $t('events.donation.dont_have_donation') }}</li>
                </ul>
            </div>
        </div>
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="ui-block">
                <div class="ui-block-title">
                    <h6 class="title">{{ $t('events.donation.list_of_donations_not_accepted') }}</h6>
                </div>
                <ul class="notification-list">
                    <li v-for="donation in donationsHaveNotReceived">
                        <div class="author-thumb">
                            <img :src="donation.user ? donation.user.image_thumbnail : '/images/faved-page8.jpg'" alt="author" style="max-height: 100%">
                        </div>
                        <div class="notification-event">
                            <router-link
                                :to="{name: 'user.timeline', params: { slug: donation.user ? donation.user.slug : null }}">
                                <a href="javascript:void(0)" class="h6 notification-friend">
                                    {{ donation.donor_name || donation.user.name }}
                                </a>
                            </router-link>
                            {{ $t('events.donation.donated')
                                + " " + donation.value
                                + " " + type.quantity
                                + " " + type.donate
                            }}.
                            <span class="notification-date">
                                <time class="entry-date updated">{{ donation.donated_at }}</time>
                            </span>
                        </div>
                        <span class="notification-icon not-accepted">
                            <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                        </span>
                    </li>
                    <li v-show="!donationsHaveNotReceived.length">{{ $t('events.donation.dont_have_donation') }}</li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
    import { mapActions, mapState } from 'vuex'
    export default {
        computed: {
            ...mapState('event', ['event']),
            donations() {
                let goals = this.event.complete_percent.filter(goal => goal.id == this.$route.params.id)[0]
                return goals.donations.filter(donation => donation.status == 1)
            },
            received() {
                return this.donations.reduce((sum, value) => sum + value.value, 0)
            },
            type() {
                const goal = this.event.complete_percent.filter(goal => goal.id == this.$route.params.id)[0]
                return {
                    donate: goal.donation_type.name,
                    quantity: goal.donation_type.quality.name
                }
            },

            donationsHaveNotReceived() {
                let goals = this.event.complete_percent.filter(goal => goal.id == this.$route.params.id)[0]
                return goals.donations.filter(donation => donation.status == 0)
            }
        }
    }
</script>
<style lang="scss" scoped>
    .row {
        width: 100%;
        margin-right: 0;
        margin-left: 0;
        .accepted {
            color: #ff5e3a;
        }

        .not-accepted {
            color: #38a9ff;
        }
    }
</style>
