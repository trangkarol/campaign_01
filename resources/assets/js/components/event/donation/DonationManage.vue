<template>
    <div class="col-md-12">
        <div class="ui-block">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('events.donation.list_support') }}</h6>
                <a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
            </div>
            <ul class="notification-list">
                <li v-for="donation in donations.not_received">
                    <div class="author-thumb">
                        <img :src="donation.user ? donation.user.image_thumbnail : '/images/faved-page8.jpg'" alt="author" style="max-height: 100%">
                    </div>
                    <div class="notification-event">
                        <router-link
                            :to="{ name: 'user.timeline', params: { slug: donation.user ? donation.user.slug : null }}"
                            class="h6 notification-friend">{{ donation.donor_name || donation.user.name }}
                        </router-link>
                        {{ $t('events.donation.donate')
                            + " " + donation.value + " "
                            + type.quantity
                            + " " + type.donate
                        }}.
                        <span class="notification-date">
                            <time class="entry-date updated" :datetime="donation.created_at">{{ donation.donated_at }}
                            </time>
                        </span>
                    </div>
                    <span class="notification-icon">
                        <div class="togglebutton">
                            <label>
                                <input
                                    type="checkbox"
                                    @change="confirmDonate"
                                    :data-id="donation.id"
                                    :data-value="donation.value"
                                    :data-time="donation.donated_at"
                                    :data-user="donation.donor_name || donation.user.name">
                            </label>
                        </div>
                    </span>
                </li>
                <li v-show="!donations.not_received.length">{{ $t('events.donation.dont_have_donation') }}</li>
            </ul>
        </div>
        <div class="ui-block" v-show="donations.received.length">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('events.donation.list') }}</h6>
                <a href="#" class="more"><svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg></a>
            </div>
            <ul class="notification-list">
                <li v-for="donation in donations.received">
                    <div class="author-thumb">
                        <img :src="donation.user ? donation.user.image_thumbnail : '/images/faved-page8.jpg'" alt="author" style="max-height: 100%">
                    </div>
                    <div class="notification-event">
                        <router-link
                            :to="{ name: 'user.timeline', params: { slug: donation.user ? donation.user.slug : null }}"
                            class="h6 notification-friend">{{ donation.donor_name || donation.user.name }}
                        </router-link>
                        {{ $t('events.donation.donate')
                            + " " + donation.value
                            + " " + type.quantity
                            + " " + type.donate
                        }}.
                        <span class="notification-date">
                            <time class="entry-date updated" :datetime="donation.created_at">{{ donation.donated_at }}
                            </time>
                        </span>
                    </div>
                    <span class="notification-icon">
                        <div class="togglebutton">
                            <label>
                                <input type="checkbox" checked="" disabled="">
                            </label>
                        </div>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
    import { mapActions, mapState } from 'vuex'
    import Noty from 'noty'
    import { get } from '../../../helpers/api'
    import store from '../../../store'

    export default {
        computed: {
            ...mapState('event', ['event']),
            donations() {
                let goals = this.event.complete_percent.filter(goal => goal.id == this.$route.params.id)[0]
                return {
                    received: goals.donations.filter(donation => donation.status == 1),
                    not_received: goals.donations.filter(donation => donation.status == 0)
                }
            },
            type() {
                const goal = this.event.complete_percent.filter(goal => goal.id == this.$route.params.id)[0]
                return {
                    donate: goal.donation_type.name,
                    quantity: goal.donation_type.quality.name
                }
            }
        },
        methods: {
            ...mapActions('event', ['change_donate_status']),
            confirmDonate(e) {
                let target = $(e.currentTarget)
                let n = new Noty({
                    text: `<p>${this.$t('events.donation.accept_confirm')}</p>
                        <p>${this.$t('events.donation.donate')}: ${target.data('value')} ${this.type.quantity} ${this.type.donate}</p>
                        <p>${this.$t('events.donation.donate_by')}: ${target.data('user')}</p>
                        <p>${this.$t('events.donation.donate_at')}: ${target.data('time')}</p>`,
                    modal: true,
                    layout: 'center',
                    closeWith: 'button',
                    buttons: [
                        Noty.button(this.$t('events.donation.yes'), 'btn-upper btn btn-primary btn--half-width', () => {
                            this.change_donate_status({
                                id: target.attr('data-id'),
                                status: 1
                            }).then(
                                this.$socket.emit('accept_donation', {
                                    donate: {
                                        donate_id: target.attr('data-id'),
                                        status: 1,
                                        goal_id: this.$route.params.id
                                    },
                                    room: `event${this.event.id}`
                                }),
                                n.close(),
                                target.prop('checked', false)
                            )
                        }),
                        Noty.button(this.$t('events.donation.no'), 'btn-upper btn btn-secondary btn--half-width', () => {
                            target.prop('checked', false)
                            n.close()
                        })
                    ]
                }).show();
            }
        },
        created() {
            if (!this.event.manage) {
                this.$router.replace('/not-found')
            }
        },
        mounted() {
            $.material.init()
        },
        updated() {
            $.material.init()
        }
    }
</script>
<style lang="scss" scoped>
    .notification-list .notification-icon {
        &:hover, &:active {
            fill: #515365;
            cursor: pointer;
        }
    }
    .btn--half-width {
        margin-left: 5px;
    }
    #noty_layout__center {
        width: 500px;
    }
    .btn-upper {
        text-transform: uppercase;
    }
</style>
