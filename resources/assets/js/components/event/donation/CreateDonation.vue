<template>
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block">
                    <div class="ui-block-title">
                        <h6 class="title">{{ $t('events.donation.import_donation') }}</h6>
                    </div>
                    <div class="ui-block-content">
                        <form>
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                    <fieldset class="form-group label-floating is-select">
                                        <multiselect
                                            v-model="selectedUser"
                                            :options="activeMember"
                                            :allow-empty="false"
                                            track-by="name"
                                            deselect-label=""
                                            :placeholder="$t('events.donation.select_or_leave_blank')"
                                            label="name">
                                        </multiselect>
                                    </fieldset>
                                    <fieldset class="form-group label-floating is-select" :class="{ 'has-danger': errors.has('name') }">
                                        <label class="control-label">{{ $t('events.donation.donor_name') }}</label>
                                        <input type="text" name="name" v-model="selectedUser.name" class="form-control"
                                            :disabled="!!selectedUser.id" v-validate="'required|max:255'">
                                        <span v-show="errors.has('name')" class="material-input text-danger">
                                            {{ errors.first('name') }}
                                        </span>
                                    </fieldset>

                                    <fieldset class="form-group label-floating is-select" :class="{ 'has-danger': errors.has('email') }">
                                        <label class="control-label">{{ $t('events.donation.donor_email') }}</label>
                                        <input type="text" name="email" v-model="selectedUser.email" class="form-control"
                                            :disabled="!!selectedUser.id" v-validate="'email|max:255'">
                                        <span v-show="errors.has('email')" class="material-input text-danger">
                                            {{ errors.first('email') }}
                                        </span>
                                    </fieldset>

                                    <fieldset class="form-group label-floating is-select" :class="{ 'has-danger': errors.has('phone') }">
                                        <label class="control-label">{{ $t('events.donation.donor_phone') }}</label>
                                        <input type="text" name="phone" v-model="selectedUser.phone" class="form-control"
                                            :disabled="!!selectedUser.id" v-validate="'max:15'">
                                        <span v-show="errors.has('phone')" class="material-input text-danger">
                                            {{ errors.first('phone') }}
                                        </span>
                                    </fieldset>

                                    <fieldset class="form-group label-floating is-select" :class="{ 'has-danger': errors.has('address') }">
                                        <label class="control-label">{{ $t('events.donation.donor_address') }}</label>
                                        <input type="text" name="address" v-model="selectedUser.address" class="form-control"
                                            :disabled="!!selectedUser.id" v-validate="'max:255'">
                                        <span v-show="errors.has('address')" class="material-input text-danger">
                                            {{ errors.first('address') }}
                                        </span>
                                    </fieldset>
                                </div>

                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                    <fieldset class="form-group label-floating is-select">
                                        <label class="control-label">{{ $t('events.donation.select_type') }}</label>
                                        <select class="selectpicker form-control" size="auto" multiple="" v-model="goals">
                                            <option :value="goal.id" v-for="goal in dataGoals">{{ goal.donation_type.name }}</option>
                                        </select>
                                    </fieldset>

                                    <div class="row">
                                        <template v-for="(goal, index) in goals">
                                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                <fieldset class="form-group label-floating" :class="{ 'has-danger': errors.has('donation_' + Number(index+1)) }">
                                                    <label class="control-label">{{ getGoalById(goal).donation_type.name }} ({{ getGoalById(goal).donation_type.quality.name }})</label>
                                                    <input type="text" v-model="values[index]" :name="'donation_' + Number(index+1)" v-validate="'required|decimal:2|min_value:0.5'">
                                                    <span v-show="errors.has('donation_' + Number(index+1))" class="material-input text-danger">
                                                        {{ errors.first('donation_' + Number(index+1)) }}
                                                    </span>
                                                </fieldset>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                <div class="switcher-block">
                                                    <div class="h6 title">{{ $t('events.donation.donation_status') }}</div>
                                                    <div class="togglebutton blue">
                                                        <label>
                                                            <input type="checkbox" v-model="status[index]">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <fieldset v-show="goals.length" class="form-group label-floating" :class="{ 'has-danger': errors.has('note') }">
                                        <label class="control-label">{{ $t('events.donation.note') }}</label>
                                        <textarea name="note" class="form-control" v-model="note" v-validate="'max:255'"></textarea>
                                        <span v-show="errors.has('note')" class="material-input text-danger">
                                            {{ errors.first('note') }}
                                        </span>
                                    </fieldset>

                                </div>
                                <div class="col-lg-8 offset-lg-2">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <button @click.prevent="handleRequest(true)" class="btn btn-blue btn-md full-width" :disabled="!goals.length">Gởi và chuyển đến trang chi tiết</button>
                                        </div>
                                        <div class="col-lg-6">
                                            <button @click.prevent="handleRequest()" class="btn btn-blue btn-md full-width" :disabled="!goals.length">Gởi và tiếp tục</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'
    import { mapActions, mapState } from 'vuex'
    import noty from '../../../helpers/noty'

    export default {
        data () {
          return {
            pageType: 'event',
            selectedUser: {
                name: '',
                email: '',
                phone: '',
                address: ''
            },
            goals: [],
            values: [],
            status: [],
            note: ''
          }
        },
        computed: {
            ...mapState('campaign', ['campaign']),
            ...mapState('event', ['dataGoals', 'event']),
            ...mapState('auth', ['user']),
            activeMember() {
                let members = this.campaign.user.filter(user => user.status == true && user.pivot.status == true && user.pivot.role_id != 6)
                return [{ name: '...', email: '', phone: '', address: '' }, ...members]
            },
            sendUser() {
                if (this.selectedUser.id)
                    return {
                        user_id: this.selectedUser.id
                    }

                return {
                    'donor_name': this.selectedUser.name,
                    'donor_email': this.selectedUser.email,
                    'donor_phone': this.selectedUser.phone,
                    'donor_address': this.selectedUser.address,
                }
            }
        },
        methods: {
            ...mapActions('event', ['update_donate']),
            getGoalById(id) {
                return this.dataGoals.filter(goal => goal.id == id)[0]
            },
            handleRequest(redirect = false) {
                this.$validator.validateAll().then((result) => {
                    this.update_donate({
                        'event_id': this.pageId,
                        'goal_id': this.goals,
                        value: this.values,
                        status: this.status,
                        note: this.note,
                        'recipient_id': this.user.id,
                        ...this.sendUser
                    })
                        .then(() => {
                            noty({ text: this.$t('messages.message-success'), force: true, container: false, type: 'success'})
                            if (redirect) {
                                this.$router.push({ name: 'event.donation', params: { slugEvent: this.pageId }})
                            } else {
                                this.selectedUser = {
                                    name: '',
                                    email: '',
                                    phone: '',
                                    address: ''
                                }
                                this.values = []
                                this.status = []
                                this.note = ''
                                setTimeout(() => {
                                    this.errors.clear()
                                }, 0)
                            }
                        })
                    .catch(() => {
                        noty({ text: this.$t('messages.error'), force: true, container: false })
                    })

                })
            }
        },
        watch: {
            goals(newGoals) {
                this.values.length = newGoals.length
                this.status.length = newGoals.length
            }
        },
        created() {
            if (!this.event.manage) {
                this.$router.replace('/not-found')
            }
        },
        mounted() {
            $.material.init()
            $('.selectpicker').selectpicker('selectAll')
        },
        updated() {
            $.material.init()
            $('.selectpicker').selectpicker()
        },
        components: {
            Multiselect
        }
    }
</script>
