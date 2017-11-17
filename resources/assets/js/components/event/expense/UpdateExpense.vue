<template>
    <div>
        <div class="background-modal">
        </div>
        <div class="modal fade show wrap-create-action" id="create-photo-album" style="display: block;">
            <div class="modal-dialog ui-block window-popup create-photo-album modal-create-action">
                <div class="ui-block-title">
                    <h6 class="title">{{ $t('events.expenses.update_expense') }}</h6>
                </div>
                <div class="ui-block-content create-expense">
                    <form>
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 select-type">
                                <label class="control-label">{{ $t('events.expenses.donate_type') }}</label>
                                <multiselect
                                    data-vv-name="type"
                                    :placeholder="$i18n.t('form.placeholder.choose_type')"
                                    :options="types"
                                    :value="getNameOfGoal(newData.goal_id)"
                                    @select="selected"
                                    @remove="removed"
                                    :searchable="false"
                                    v-validate="'required'">
                                </multiselect>
                                <span v-show="errors.has('type')" class="material-input text-danger">
                                    {{ errors.first('type') }}
                                </span>
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                <div class="form-group label-floating">
                                    <label class="control-label">{{ $t('form.label.cost') }}</label>
                                    <input
                                        class="form-control"
                                        data-vv-name="cost"
                                        type="text"
                                        v-model="newData.cost"
                                        v-validate="'required|numeric|min_value:0|max_value:' + maxCost">
                                    <span v-show="errors.has('cost')" class="material-input text-danger">
                                        {{ errors.first('cost') }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                <div class="form-group date-time-picker label-floating">
                                    <label class="control-label">{{ $t('form.label.time') }}</label>
                                    <date-picker :date.sync="time" :formatStand.sync="newData.time"></date-picker>
                                    <span class="input-group-addon">
                                        <svg class="olymp-calendar-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-calendar-icon"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <quill-editor
                                    data-vv-name="reason"
                                    id="reason"
                                    :options="editorOption"
                                    v-model="newData.reason"
                                    v-validate="'required|min:10'">
                                </quill-editor>
                                <span v-show="errors.has('reason')" class="material-input text-danger">
                                    {{ errors.first('reason') }}
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="ui-block-title">
                    <a href="javascript:void(0)" class="btn btn-secondary btn-lg btn--half-width" @click="onClose">
                        {{ $t('form.button.cancel') }}
                    </a>
                    <a href="javascript:void(0)" class="btn btn-primary btn-lg btn--half-width float-right" @click="updateExpense">
                        {{ $t('form.button.save') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import DatePicker from '../../libs/DatePicker.vue'
    import { patch, get } from '../../../helpers/api'
    import Multiselect from 'vue-multiselect'
    import noty from '../../../helpers/noty'
    export default {
        props: ['show', 'expense'],
        data: () => ({
            editorOption: {
                placeholder: 'reason',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'header': 1 }, { 'header': 2 }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'direction': 'rtl' }],
                        [{ 'size': ['small', false, 'large', 'huge'] }],
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'font': [] }],
                        [{ 'align': [] }],
                        ['clean']
                    ]
                }
            },
            types: [],
            goal: {},
            time: '',
            newData: {
                time: null,
                goal_id: null,
                event_id: null,
                cost: '',
                type: 0,
                reason: ''
            },
            dataGoals: [],
            pageType: 'event'
        }),

        watch: {
            time() {
                this.newData.time = this.newData.time? this.newData.time : this.time
            }
        },

        computed: {
            maxCost() {
                if (!Object.keys(this.goal).length) {
                    return 0
                }

                let receives = this.goal.donations.filter(donation => donation.status == 1)
                let totalReceive = receives.reduce((sum, value) => sum + value.value, 0)
                let expensed = this.goal.expenses.filter(expense => expense.id != this.expense.id)
                let totalExpense = expensed.reduce((sum, value) => sum + value.cost, 0)

                return totalReceive - totalExpense
            }
        },

        methods: {
            selected(value, id) {
                this.goal = this.dataGoals.filter(dataGoal => dataGoal.donation_type.name == value)[0]
                this.newData.goal_id = this.goal.id
                this.newData.cost = this.newData.cost === null ? '' : null
                this.newData.reason = this.newData.reason || null
            },

            removed(value, id) {
                this.newData.goal_id = null
                this.newData.cost = ''
            },

            updateExpense() {
                this.$validator.validateAll().then(result => {
                    this.$Progress.start()
                    patch(`expense/${this.expense.id}`, this.newData)
                        .then(res => {
                            this.$Progress.finish()
                            noty({
                                text: this.$i18n.t('messages.update_success'),
                                force: false,
                                container: false,
                                type: 'success'
                            })
                            this.newData.goal_id =  null
                            this.newData.cost =  ''
                            this.newData.reason = ''
                            this.$emit('loadAgainApi')
                            this.onClose()
                        })
                        .catch(err => {
                            this.$Progress.fail()
                            noty({
                                text: this.$i18n.t('messages.create_fail'),
                                type: 'error',
                                force: false,
                                container: false
                            })
                        })
                })
            },

            getNameOfGoal(id) {
                let goal = this.dataGoals.filter(goal => goal.id == id)

                if (goal.length) {
                    return goal[0].donation_type.name
                }

                return ''
            },

            callApi() {
                get(`goal?event_id=${this.pageId}`).then(res => {
                    this.dataGoals = res.data.goals
                    this.types = this.dataGoals.map(goal => goal.donation_type.name)
                    this.goal = this.dataGoals.filter(dataGoal => {
                        return dataGoal.donation_type.name == this.expense.goal.donation_type.name
                    })[0]
                })
            },

            onClose() {
                this.$emit('update:show', false)
            },
        },

        created() {
            this.newData.event_id = this.pageId
            this.newData.goal_id = this.expense.goal.id
            this.newData.cost = this.expense.cost
            this.newData.reason = this.expense.reason
            this.time = moment(this.expense.time).format('YYYY-MM-DD')
            this.callApi()
        },

        components: {
            DatePicker,
            Multiselect
        }
    }
</script>

<style lang="scss">
    .background-modal {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1000;
        background: black;
        opacity: 0.5
    }
    .wrap-create-action {
        overflow-y: scroll;
        .modal-create-action {
            width: 960px !important;
            margin-top: 80px !important;
            .quill-editor {
                min-height: 200px;
                height: auto;
                margin: 10px 0;
                .ql-container {
                    min-height: 132px;
                    height: auto;
                    .ql-editor {
                        height: auto;
                        min-height: 132px;
                    }
                }
            }
            .upload-file {
                min-height: 300px;
                form {
                    min-height: 300px;
                }
            }
            .has-error {
                border: 1px solid red;
                border-radius: 2px;
            }
        }
        &::-webkit-scrollbar {
            display: none;
        }
        .dz-error-message {
            top: 5px !important;
            left: 59px !important;
        }
        a.btn{
            margin: 0 !important;
        }
        .float-right {
            float: right;
        }
    }
    .vue-dropzone {
        .dz-preview {
            .dz-error-mark {
                i {
                    color: red !important;
                }
            }
        }
    }
</style>
