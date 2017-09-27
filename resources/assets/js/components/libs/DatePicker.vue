<template lang="html">
    <div>
        <input type="text" class="form-control" name="birthday" :value="value" autocomplete="off" />
    </div>
</template>

<script>
import { EventBus } from '../../EventBus.js'
export default {
    props: {
        date: {
            type: String
        },
        data: {
            type: Object
        },
        formatStand: {
            type: Date
        }
    },
    data() {
        return {
            value: this.date,
            standTime: ''
        }
    },
    created() {
        EventBus.$on('changeLanguage', (data) => {
            if (this.value != '') {
                this.emitValue(window.moment(this.standTime).format('L'))
            }
        })
    },
    mounted() {
        $(this.$el).daterangepicker({
            autoUpdateInput: false,
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'L'
            },
            ...this.data,
        });

        $(this.$el).on('apply.daterangepicker', (ev, picker) => {
            const { format } = picker.locale
            const { date } = picker.startDate._d

            this.standTime = picker.startDate._d
            this.emitValue(picker.startDate.format(format))

        });

        // set null value when clear datetimepicker
        $(this.$el).on('hide.daterangepicker', (ev, picker) => {
            this.$emit('update:date', '')
        })
    },
    methods: {
        emitValue(value) {
            this.$emit('update:date', value)
            this.$emit('input', value)
            this.value = value
            this.$emit('update:formatStand', this.standTime)
        }
    },
    beforeDestroy() {
        $(this.$el).data('daterangepicker').remove()
    }
}
</script>
