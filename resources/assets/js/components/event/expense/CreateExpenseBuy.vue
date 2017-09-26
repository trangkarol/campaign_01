<template>
    <div class="ui-block-content create-expense">
        <form>
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 select-type">
                    <label class="control-label">{{ $t('events.expenses.donate_type') }}</label>
                    <multiselect
                        data-vv-name="type"
                        :placeholder="$i18n.t('form.placeholder.choose_type')"
                        :options="types"
                        :value="getNameOfGoal(newExpense.expense.goal_id)"
                        @select="selected"
                        @remove="removed"
                        v-validate="'required'">
                    </multiselect>
                    <span v-show="errors.has('type')" class="material-input text-danger">
                        {{ errors.first('type') }}
                    </span>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="form-group label-floating" :class="{ 'has-danger': this.errors.has('cost') }">
                        <label class="control-label">{{ $t('form.label.cost') }}</label>
                        <input
                            class="form-control"
                            data-vv-name="cost"
                            type="text"
                            v-model="newExpense.expense.cost"
                            v-validate="'required|numeric|min_value:0|max_value:' + maxCost">
                        <span v-show="errors.has('cost')" class="material-input text-danger">
                            {{ errors.first('cost') }}
                        </span>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="form-group date-time-picker label-floating">
                        <label class="control-label">{{ $t('form.label.time') }}</label>
                        <date-picker :date.sync="time"></date-picker>
                        <span class="input-group-addon">
                            <svg class="olymp-calendar-icon">
                                <use xlink:href="/frontend/icons/icons.svg#olymp-calendar-icon"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="form-group label-floating" :class="{ 'has-danger': this.errors.has('quantity') }">
                        <label class="control-label">{{ $t('form.label.quantity') }}</label>
                        <input
                            class="form-control"
                            data-vv-name="quantity"
                            type="text"
                            v-model="newExpense.quantity"
                            v-validate="'required|numeric|min_value:0'">
                        <span v-show="errors.has('quantity')" class="material-input text-danger">
                            {{ errors.first('quantity') }}
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 select-type">
                    <label class="control-label">{{ $t('events.expenses.product') }}</label>
                    <multiselect
                        data-vv-name="product"
                        :placeholder="$i18n.t('form.placeholder.choose_product')"
                        :options="products"
                        :value="newExpense.name"
                        @select="(value, id) => { newExpense.name = value }"
                        @remove="(value, id) => { newExpense.name = null }"
                        v-validate="'required'"
                        :searchable="true"
                        @search-change="newProduct">
                    </multiselect>
                    <span v-show="errors.has('product')" class="material-input text-danger">
                        {{ errors.first('product') }}
                    </span>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 select-type">
                    <label class="control-label">{{ $t('events.expenses.quality') }}</label>
                    <multiselect
                        data-vv-name="quality"
                        :placeholder="$i18n.t('form.placeholder.choose_product')"
                        :searchable="true"
                        :options="qualitys"
                        :value="newExpense.quality"
                        @select="(value, id) => { newExpense.quality = value }"
                        @remove="(value, id) => { newExpense.quality = null }"
                        @search-change="newQuality"
                        v-validate="'required'">
                    </multiselect>
                    <span v-show="errors.has('quality')" class="material-input text-danger">
                        {{ errors.first('quality') }}
                    </span>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <quill-editor
                        data-vv-name="reason"
                        id="reason"
                        :options="editorOption"
                        v-model="newExpense.expense.reason"
                        v-validate="'required|min:10'">
                    </quill-editor>
                    <span v-show="errors.has('reason')" class="material-input text-danger">
                        {{ errors.first('reason') }}
                    </span>
                </div>
            </div>
        </form>
        <div class="btn-create-expense">
            <button
                href="#"
                class="btn btn-primary btn-md-2 bg-breez"
                data-toggle="modal"
                data-target="#update-header-photo"
                @click="create('expense.list')">
                {{ $t('events.expenses.save_goto_list') }}
            </button>
            <button
                href="#"
                class="btn btn-primary btn-md-2 bg-breez"
                data-toggle="modal"
                data-target="#update-header-photo"
                @click = "create('expense.create.buy')">
                {{ $t('events.expenses.save_create_new_other') }}
            </button>
        </div>
    </div>
</template>

<script type="text/javascript">
    import DatePicker from '../../libs/DatePicker.vue'
    import { post, get } from '../../../helpers/api'
    import Multiselect from 'vue-multiselect'
    import noty from '../../../helpers/noty'
    export default {
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
            products: [],
            qualitys: [],
            newExpense: {
                expense: {
                    time: null,
                    goal_id: null,
                    event_id: null,
                    cost: '',
                    type: 1,
                    reason: ''
                },
                name: '',
                quality: '',
                quantity: ''
            },
            dataGoals: [],
            pageType: 'event'

        }),

        watch: {
          time() {
              var arrayTime = this.time.split('/')

              if (arrayTime[2].length == 4) {
                  this.newExpense.expense.time = this.time.split('/').reverse().join('-')
              } else {
                  this.newExpense.expense.time = this.time.split('/').join('-')
              }
          }
        },

        computed: {
            maxCost() {
                if (!Object.keys(this.goal).length) {
                    return 0
                }

                let receives = this.goal.donations.filter(donation => donation.status == 1)
                let totalReceive = receives.reduce((sum, value) => sum + value.value, 0)
                let totalExpense = this.goal.expenses.reduce((sum, value) => sum + value.cost, 0)

                return totalReceive - totalExpense
            }
        },

        methods: {
            selected(value, id) {
                this.goal = this.dataGoals.filter(dataGoal => dataGoal.donation_type.name == value)[0]
                this.newExpense.expense.goal_id = this.goal.id
                this.newExpense.expense.cost = this.newExpense.expense.cost === null ? '' : null
                this.newExpense.expense.reason = this.newExpense.expense.reason || null
                this.newExpense.quantity = this.newExpense.quantity || null
                this.newExpense.name = this.newExpense.name || null
                this.newExpense.quality = this.newExpense.quality || null
            },

            removed(value, id) {
                this.newExpense.expense.goal_id = null
                this.newExpense.expense.cost = ''
            },

            newProduct(newValue) {
                if (newValue != '' && this.products.indexOf(newValue) < 0) {
                    this.products.push(newValue)
                }
            },

            newQuality(newValue) {
                if (newValue != '' && this.qualitys.indexOf(newValue) < 0) {
                    this.qualitys.push(newValue)
                }
            },

            create(nameRouter) {
                this.$validator.validateAll().then(result => {
                    this.$Progress.start()
                    post('expense-create-buy', this.newExpense)
                        .then(res => {
                            this.$Progress.finish()
                            noty({
                                text: this.$i18n.t('messages.create_success'),
                                force: false,
                                container: false,
                                type: 'success'
                            })
                            this.newExpense.expense.goal_id =  null
                            this.newExpense.expense.cost =  ''
                            this.newExpense.expense.reason = ''
                            this.newExpense.quantity = ''
                            this.newExpense.quality = ''
                            this.newExpense.name = ''
                            this.callApi()
                            this.$router.push({ name: nameRouter, params: { event_id: this.pageId }})
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
                })
            }
        },

        created() {
            this.callApi()
            this.newExpense.expense.event_id = this.pageId
            this.time = moment().format('L')

            get('event/donation').then(res => {
                this.qualitys = res.data.qualitys.map(quality => quality.name)
            })

            get('product').then(res => {
                this.products = res.data.products.map(product => product.name)
            })
        },

        components: {
            DatePicker,
            Multiselect
        }
    }
</script>

<style lang="scss">
    .create-expense {
        background-color: white;
        .multiselect__tags {
            padding-top: 20px;
            input {
                padding-left: 15px !important;
            }
        }
        .select-type {
            position: relative;
            label {
                position: absolute;
                z-index: 9;
                top: 6px;
                left: 24px;
                font-size: 11px;
            }
        }
        .btn-create-expense {
            width: 100%;
            text-align: center;
            button {
                margin-bottom: 0px;
            }
        }
        .daterangepicker {
            top: 786.013px !important;
        }
        .quill-editor {
            margin-bottom: 5px;
        }
    }
</style>
