<template lang="html">
    <social-sharing
        v-show="isSocialSharing"
        :url="urlShare"
        :title="title"
        :description="description"
        inline-template>
        <div class="box-btn-share">
            <div class="position">
                <div class="text-share">{{ $t('homepage.share_to') }}</div>
                <div class="flex-social">
                    <network network="facebook" class="btn btn-control has-i btn-social bg-facebook">
                        <i class="fa fa-facebook"></i>
                        <div class="ripple-container"></div>
                    </network>
                    <network network="googleplus" class="btn btn-control has-i btn-social bg-google">
                        <i class="fa fa-google-plus"></i>
                    </network>
                    <network network="twitter" class="btn btn-control has-i btn-social bg-twitter">
                        <i class="fa fa-twitter"></i>
                    </network>
                </div>
            </div>
        </div>
    </social-sharing>
</template>

<script>
const inBrowser = typeof window !== 'undefined'
export default {
    props: {
        /**
         * URL to share.
         * @var mix
         */
        url: {
            default: inBrowser ? window.location.href : ''
        },

        /**
         * Sharing title, if available by network.
         * @var string
         */
        title: {
            type: String,
            default: ''
        },

        /**
         * Sharing description, if available by network.
         * @var string
         */
        description: {
            type: String,
            default: ''
        },

        /**
         * Facebook quote
         * @var string
         */
        quote: {
            type: String,
            default: ''
        },

        /**
         * Twitter hashtags
         * @var string
         */
        hashtags: {
            type: String,
            default: ''
        },

        /**
         * ON/OFF popup network sharing
         * @var boolean
         */
        isSocialSharing: {
            type: Boolean,
            default: false
        },
    },
    computed: {
        urlShare() {
            return typeof this.url === 'object'
                ? window.Laravel.url + this.$router.resolve(this.url).href
                : this.url
        }
    }
}
</script>
