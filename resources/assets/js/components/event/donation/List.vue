<template>
    <div>
        <transition name="bounce">
            <table class="table ui-block" v-show="donations.length">
                <thead>
                    <tr>
                        <th>#</th>
                        <th></th>
                        <th>{{ $t('form.label.name') }}</th>
                        <th>{{ $t('events.donation.donate') }}</th>
                        <th>{{ $t('events.donation.donate_at') }}</th>
                        <th>{{ $t('events.donation.status') }}</th>
                        <th>{{ $t('events.donation.note') }}</th>
                        <th>{{ $t('user.search.view_more') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(donation, index) in donations" :key="donation.id">
                        <th scope="row">{{ index + 1 }}</th>
                        <td>
                            <span class="author-thumb">
                                <img :src="donation.user ? donation.user.image_thumbnail : '/images/faved-page8.jpg'" alt="author" style="max-width: 36px">
                            </span>
                        </td>
                        <td>
                            <router-link
                                :to="{ name: 'user.timeline', params: { slug: donation.user ? donation.user.slug : null }}"
                                class="h6 notification-friend">
                                {{ donation.donor_name || donation.user.name }}
                            </router-link>
                        </td>
                        <td>
                            {{ donation.value + ' ' + donation.goal.donation_type.quality.name + ' ' + donation.goal.donation_type.name }}
                        </td>
                        <td><timeago :since="donation.created_at"/></td>
                        <td>{{ donation.status ? $t('events.donation.confirmed') : $t('events.donation.unconfimred') }}</td>
                        <td>{{ donation.note }}</td>
                        <td>
                            <div class="more">
                                <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                <ul class="more-dropdown" style="width: 100px">
                                    <li><a href="#" @click.prevent="view(donation)">{{ $t('user.search.view_more') }}</a></li>
                                    <li><a href="#" v-if="(!!user && donation.user_id == user.id && donation.status == 0) || event.manage"
                                        @click.prevent="confirmDelete(donation)">
                                        {{ $t('form.delete') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </transition>
        <modal :show.sync="showView">
            <h4 slot="header">{{ $t('homepage.infomation') }}</h4>
            <div slot="main">
                <table class="table table-bordered" style="margin-top: 1em">
                    <tbody>
                        <td colspan="4" class="text-center text-uppercase"><strong>{{ $t('events.donation.donor') }}</strong></td>
                        <tr v-for="(value, key) in selectedUser">
                            <th scope="row">{{ $t(`events.donation.${key}`) }} <template v-if="!selected.user_id && event.manage">(*)</template></th>
                            <td v-if="!show.includes(key)" @click.ctrl="(!selected.user_id && event.manage) ? showUpdateForm(key, value) : null">{{ value }}</td>
                            <td v-else>
                                <fieldset :class="{ 'has-danger': errors.has(`${key}`) }">
                                    <input type="text"
                                        v-model="updateData[key]"
                                        :name="key"
                                        v-validate="validate[key]"
                                        class="form-control input-sm has-danger">
                                    <span v-show="errors.has(`${key}`)" class="material-input text-danger">
                                        {{ errors.first(`${key}`) }}
                                    </span>
                                </fieldset>
                            </td>
                        </tr>
                        <td colspan="4" class="text-center text-uppercase"><strong>{{ $t('events.donation.donation_details') }}</strong></td>
                        <tr v-for="(value, key) in selectedDonation">
                            <th scope="row">{{ $t(`events.donation.${key}`) }} <template v-if="canEdit  && key != 'status' || event.manage">(*)</template></th>
                            <td v-if="!show.includes(key)" @click.ctrl="canEdit && key != 'status' || event.manage ? showUpdateForm(key, value) : null">{{ value }}</td>
                            <td v-else>
                                <fieldset :class="{ 'has-danger': errors.has(`${key}`) }">
                                    <textarea
                                        rows="2"
                                        class="form-control"
                                        v-if="key == 'note'"
                                        v-model="updateData[key]"
                                        :name="key"
                                        v-validate="validate[key]">
                                    </textarea>
                                    <input class="form-control" type="checkbox" v-model="updateData[key]" v-else-if="key == 'status'">
                                    <select class="form-control input-sm" size="small" v-else-if="key == 'goal_id'" v-model="updateData[key]">
                                        <option v-for="goal in goals" :value="goal.id">
                                            {{ goal.donation_type.name }}
                                        </option>
                                    </select>
                                    <input
                                        type="text"
                                        :ref="key"
                                        class="form-control input-sm"
                                        :name="key"
                                        v-validate="validate[key]"
                                        v-model="updateData[key]"
                                        v-else>
                                    <span v-show="errors.has(`${key}`)" class="material-input text-danger">
                                        {{ errors.first(`${key}`) }}
                                    </span>
                                </fieldset>
                            </td>
                        </tr>
                        <td colspan="4" align="center" v-if="!!show.length">
                            <button @click="showView = false" class="btn btn-secondary" style="margin-bottom: 0">{{ $t('form.cancel') }}</button>
                            <button @click="handleUpdate" class="btn btn-primary" style="margin-bottom: 0">{{ $t('form.button.save') }}</button>
                        </td>
                        <tr v-if="canEdit || event.manage">
                            <td colspan="4" align="right" v-html="$t('events.donation.dblclick_to_edit')"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </modal>
        <confirm :show.sync="showDelete" :messages="$t('events.donation.confirm_delete_donation')" @handelMethod="handleDelete"></confirm>
    </div>
</template>
<script>
    import Modal from '../../libs/Modal'
    import { mapState, mapActions } from 'vuex'
    import { patch, del } from '../../../helpers/api.js'
    import Confirm from '../../libs/MessageComfirm'
    import noty from '../../../helpers/noty.js'
    export default {
        data() {
            return {
                showView: false,
                selected: {},
                updateData: {},
                show: [],
                showDelete: false,
                donations: this.data,
                validate: {
                    donor_name: 'required|max:255',
                    donor_email: 'email|max:255',
                    donor_phone: 'max:15',
                    donor_address: 'max:255',
                    value: 'required|decimal:2|min_value:0.5',
                    note: 'max:255'
                }
            }
        },
        props: {
            data: {
                type: Array,
                required: true
            },
        },
        computed: {
            ...mapState('event', ['event']),
            ...mapState('auth', ['user']),
            goals() {
                return this.event.complete_percent
            },
            canEdit() {
                return !!this.user && this.selected.user_id == this.user.id && this.selected.status == 0
            },
            selectedUser() {
                if (Object.keys(this.selected).length !== 0) {
                    return {
                        donor_name: this.selected.donor_name || this.selected.user.name,
                        donor_email: this.selected.user ? this.selected.user.email : this.selected.donor_email,
                        donor_phone: this.selected.user ? this.selected.user.phone : this.selected.donor_phone,
                        donor_address: this.selected.user ? this.selected.user.address : this.selected.donor_address
                    }
                }
            },
            selectedDonation() {
                if (Object.keys(this.selected).length !== 0) {
                    return {
                        value: this.selected.value,
                        goal_id: `${this.selected.goal.donation_type.quality.name} ${this.selected.goal.donation_type.name}`,
                        status: this.selected.status ? this.$t('events.donation.confirmed') : this.$t('events.donation.unconfimred'),
                        note: this.selected.note
                    }
                }
            }
        },
        watch: {
            showView(newData) {
                if (newData == false) {
                    this.show = []
                    this.updateData = {}
                }
            },
            data(newData) {
                this.donations = newData
            }
        },
        methods: {
            ...mapActions('event', ['setDonationList']),
            view(donation) {
                this.selected = donation
                this.showView = true
            },
            confirmDelete(donation) {
                this.selected = donation
                this.showDelete = true
            },
            handleDelete() {
                del(`donation/donation/${this.selected.id}`)
                    .then(res => {
                        let donationIndex = this.donations.findIndex(d => d.id == this.selected.id)
                        this.showDelete = false
                        this.donations.splice(donationIndex, 1)
                        // remove donation from vuex store
                        let cloneGoals = _.cloneDeep(this.goals)
                        let goalId = cloneGoals.findIndex(goal => goal.id == this.selected.goal_id)
                        let donations = cloneGoals[goalId].donations
                        donations.splice(donations.findIndex(d => d.id == this.selected.id), 1)
                        this.setDonationList(cloneGoals)
                        noty({ text: this.$t('messages.message-success'), force: true, container: false, type: 'success'})
                    })
                    .catch(err => noty({ text: this.$t('messages.error'), force: true, container: false }))
            },
            showUpdateForm(key, value) {
                this.show.push(key)
                this.$set(this.updateData, key, value)
                if (key == 'status')
                    this.$set(this.updateData, key, this.selected.status)
                if (key == 'goal_id')
                    this.$set(this.updateData, key, this.selected.goal_id)
            },
            handleUpdate() {
                this.$validator.validateAll().then((result) => {
                    patch(`donation/donation/${this.selected.id}`, this.updateData)
                        .then(res => {
                            res.data.donation.value = +res.data.donation.value
                            let donationIndex = this.donations.findIndex(d => d.id == this.selected.id)
                            this.donations.splice(donationIndex, 1, res.data.donation)
                            this.showView = false
                            // update donation from vuex store
                            let cloneGoals = _.cloneDeep(this.goals)
                            let goalId = cloneGoals.findIndex(goal => goal.id == this.selected.goal_id)
                            let donations = cloneGoals[goalId].donations
                            donations.splice(donations.findIndex(d => d.id == this.selected.id), 1, res.data.donation)
                            this.setDonationList(cloneGoals)
                            noty({ text: this.$t('messages.message-success'), force: true, container: false, type: 'success'})
                        })
                        .catch(err => noty({ text: this.$t('messages.error'), force: true, container: false }))
                })
            }
        },
        components: {
            Modal,
            Confirm
        }
    }
</script>
<style lang="scss">
    .bounce-enter-active {
      animation: bounce-in .5s;
    }
    .bounce-leave-active {
      animation: bounce-in .5s reverse;
    }
    @keyframes bounce-in {
      0% {
        transform: scale(0);
      }
      50% {
        transform: scale(1.1);
      }
      100% {
        transform: scale(1);
      }
    }
    .input-sm {
        padding: 0.5em;
    }
</style>
