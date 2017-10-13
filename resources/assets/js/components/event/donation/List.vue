<template>
    <transition name="fade">
        <div class="ui-block" v-show="data.length">
            <div class="ui-block-title">
                <h6 class="title">{{ $t('events.donation.list_support') }}</h6>
            </div>
            <ul class="notification-list">
                <li v-for="donation in data">
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
                            + " " + donation.goal.donation_type.quality.name
                            + " " + donation.goal.donation_type.name
                        }}.
                        <span class="notification-date">
                            <time class="entry-date updated" :datetime="donation.created_at">{{ donation.donated_at }}
                            </time>
                        </span>
                    </div>
                    <span class="notification-icon">
                        <!--cho nay con thieu -->
                    </span>
                </li>
            </ul>
        </div>
    </transition>
</template>
<script>
    export default {
        props: {
            data: {
                type: Array,
                required: true
            },
        }
    }
</script>
<style lang="scss">
    .fade-enter-active,
    .fade-leave-active {
      transition: opacity .5s
    }
    .fade-enter,
    .fade-leave-to {
      opacity: 0
    }
</style>
