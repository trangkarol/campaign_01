<template>
    <time v-text="time"></time>
</template>

<script>
    import { EventBus } from '../../EventBus.js'
    export default {
        data() {
            return {
                time: moment(this.since, "YYYY-MM-DD h:mm:ss").fromNow()
            }
        },
        props: {
            since: {
                type: String,
                required: true
            }
        },
        created() {
            EventBus.$on('changeLanguage', ({ locale }) => {
                window.moment.locale(locale)
                this.time = moment(this.since, "YYYY-MM-DD h:mm:ss").fromNow()
            })
        }
    }
</script>
