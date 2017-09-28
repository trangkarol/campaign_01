<template lang="html">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <div class="form-group date-time-picker label-floating is-focused">
                <label class="control-label">{{ $t('campaigns.startday') }}</label>
                <date-picker :date.sync="start" :formatStand.sync="standStart">
                </date-picker>
                <span class="input-group-addon">
                    <svg class="olymp-calendar-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-calendar-icon"></use>
                    </svg>
                </span>
                <span class="material-input text-danger">
                    {{ messageStartDay }}
                </span>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <div class="form-group date-time-picker label-floating is-focused">
                <label class="control-label">{{ $t('campaigns.endday') }}</label>
                <date-picker :date.sync="end" :formatStand.sync="standEnd">
                </date-picker>
                <span class="input-group-addon">
                    <svg class="olymp-calendar-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-calendar-icon"></use>
                    </svg>
                </span>
                <span class="material-input text-danger">
                    {{ messageEndDay }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import DatePicker from '../libs/DatePicker.vue'

export default {
    data() {
        return {
            messageEndDay: '',
            messageStartDay: '',
            start: this.startDay,
            end: this.endDay,
            status: '',
            standStart: null,
            standEnd: null
        }
    },
    props: {
        flag: Boolean,
        startDay: String,
        endDay: String,
        isUpdate: {
            type : Boolean,
            default: false
        }
    },
    watch: {
        start() {
            this.status = this.validateDate(this.start, this.end)
            this.$emit('update:startDay', this.standStart)
        },
        end() {
            this.status = this.validateDate(this.start, this.end)
            this.$emit('update:endDay', this.standEnd)
        },
        status() {
            this.$emit('update:flag', this.status)
        }
    },
    methods: {
        validateDate(start, end) {
            let flag = true

            if (!start) {
                this.start = window.moment().format(this.$i18n.t('campaigns.format_date'))
                this.messageStartDay = ''
                this.messageEndDay = ''
            }

            if (window.moment(this.standStart).isBefore(moment()) && !this.isUpdate) {
                this.messageStartDay = this.$i18n.t('messages.start_day')
                flag = false
            } else {
                this.messageStartDay = ''
            }

            if (end && (window.moment(this.standEnd).isSameOrBefore(moment())
                || window.moment(this.standEnd).isSameOrBefore(this.standStart))
            ) {
                this.messageEndDay = this.$i18n.t('messages.end_day')
                flag = false
            } else {
                this.messageEndDay = ''
            }

            return flag
        }
    },
    components: {
        DatePicker
    }
}
</script>
