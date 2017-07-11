<template lang="html">
    <div class="fade show" id="create-event" style="display: block">
        <div class="modal-dialog ui-block window-popup create-event">
            <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                <svg class="olymp-close-icon"><use xlink:href="icons/icons.svg#olymp-close-icon"></use></svg>
            </a>

            <div class="ui-block-title">
                <h6 class="title">{{ $t('events.create_event') }}</h6>
            </div>
            <div class="ui-block-content">
                <div class="form-group label-floating" :class="{ 'has-danger': errors.has('name')}">
                    <label class="control-label">{{ $t('form.label.event_name') }}</label>
                    <input
                        name="name"
                        class="form-control"
                        type="text"
                        minlength="20"
                        maxlength="255"
                        v-model="newEvent.title"
                        v-validate="'required|max:255'">
                    <span v-show="errors.has('name')" class="material-input text-danger">
                        {{ errors.first('name') }}
                    </span>
                </div>

                <div class="form-group label-floating">
                    <label class="control-label">{{ $t('form.label.event_location') }}</label>
                    <gmap-autocomplete :value="newEvent.address" class="form-control" @place_changed="setPlace"></gmap-autocomplete>
                    <span class="material-input"></span>
                </div>

                <div class="form-group label-floating">
                    <gmap-map :center="center" :zoom="zoom" style="width: 100%; height: 500px" v-if="showMap">
                        <gmap-marker
                            :position="center"
                            :clickable="true"
                            :draggable="true"
                            @click="center"
                            @dragend="updatePosition($event)">
                        </gmap-marker>
                    </gmap-map>
                </div>

                <setting-date
                    :startDay.sync="startDate"
                    :endDay.sync="endDate"
                    :flag.sync="flag">
                </setting-date>

                <quill-editor
                    data-vv-name="description"
                    id="description"
                    :options="editorOption"
                    v-model="newEvent.description"
                    v-validate="'required'">
                </quill-editor>
                <span v-show="errors.has('description')" class="material-input text-danger">
                    {{ errors.first('description') }}
                </span>

                <div class="wrap-donation">
                    <p>{{ $t('form.title.add_donation') }}
                        <i class="fa fa-plus-square icon-donation" aria-hidden="true" id="add-donation" @click="addDonation">
                        </i>
                    </p>
                    <donations
                        v-for="(donation, index) in donations"
                        :donation="donation"
                        :index="index"
                        :key="index"
                        :visible="visible"
                        @add-instance-validate="addErrors"
                        @update-row-donation="updateGoal"
                        @delete-donation="deleteDonation(index)">
                    </donations>
                </div>

                <div class="form-group label-floating">
                    <p>Upload image</p>
                    <dropzone
                        ref="vueDropzone"
                        id="myVueDropzone"
                        url="/api/file/upload"
                        acceptedFileTypes='image/*'
                        :autoProcessQueue="true"
                        :maxNumberOfFiles="maxFile"
                        :maxFileSizeInMB='maxSizeFile'
                        @vdropzone-success="showSuccess"
                        @vdropzone-removed-file="deleteFile">
                        <input type="hidden" name="token" value="file">
                    </dropzone>
                </div>
                <button class="btn btn-breez btn-lg full-width" @click="createEvent">{{ $t('form.button.create_event') }}</button>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    import * as VueGoogleMaps from 'vue2-google-maps'
    import Dropzone from 'vue2-dropzone'
    import Vue from 'vue'
    import Donations from './Donations.vue'
    import SettingDate from '../libs/SettingDate.vue'
    import axios from 'axios'
    import { config, editorOption } from '../../config'
    import { post, del } from '../../helpers/api'
    import noty from '../../helpers/noty'

    Vue.use(Dropzone)
    Vue.use(VueGoogleMaps, {
        load: {
            key: config.keyMap,
            libraries: 'places',
        }
    })

    export default {
        data: () => ({
            showMap: false,
            zoom: config.zoom,
            maxFile: config.maxFileUpload,
            maxSizeFile: config.maxSizeFile,
            latLng: { lat: 0, lng: 0 },
            center: { lat: 0, lng: 0 },
            visible: true,
            startDate: '',
            endDate: '',
            flag: true,
            donations: [
                { type : '', goal: '', quality: ''}
            ],
            errorBags: {},
            newEvent : {
                title: '',
                campaign_id: '',
                description: '',
                latitude: null,
                longitude: null,
                settings: [],
                files: [],
                address: ' ',
                donations: []
            },
            editorOption
        }),

        methods: {
            addErrors(index, value) {
                this.errorBags[index] = value
            },

            setPlace(place) {
                this.latLng = {
                    lat: place.geometry.location.lat(),
                    lng: place.geometry.location.lng(),
                };
                this.newEvent.address = place.formatted_address
                this.newEvent.latitude = place.geometry.location.lat()
                this.newEvent.longitude = place.geometry.location.lat()
                this.center= {
                    lat: place.geometry.location.lat(),
                    lng: place.geometry.location.lng()
                };

                if (!this.showMap) {
                    this.showMap = true;
                }
            },

            addDonation() {
                let donation = { type : '', goal: '', quality: ''}
                this.donations.push(donation)
                this.visible = false
            },

            // filter danation before request to sever
            getDonation() {
                this.newEvent.donations = []
                for (let donation of this.donations) {
                    let flag = true

                    for (let key in donation) {
                        if (!donation[key]) {
                            flag = false
                            break
                        }
                    }

                    if (flag) {
                        this.newEvent.donations.push(donation)
                    }
                }
            },

            deleteDonation(index) {
                this.donations.length > 1 && this.donations.splice(index, 1)

                this.errorBags.length > 1 && this.errorBags.splice(index, 1)

                if (this.donations.length === 1) {
                    this.visible = true
                }
            },

            updateGoal(newValue) {
                // mix <=> key: type, goal, quality
                // key <=> key of newValue that children emitted
                const [key, mix, instanse] = Object.keys(newValue)
                const index = newValue[key]

                this.errorBags[index] = newValue[instanse]
                this.donations[index][mix] = newValue[mix]
            },

            updatePosition(event) {
                this.latLng = {
                    lat: event.latLng.lat(),
                    lng: event.latLng.lng(),
                };
                this.newEvent.latitude = event.latLng.lat();
                this.newEvent.longitude = event.latLng.lng();

                $.ajax({
                    url: `https://maps.googleapis.com/maps/api/geocode/json?latlng=
                        ${event.latLng.lat()},
                        ${event.latLng.lng()}
                        &key=${config.keyMap}`,
                    success: data => {
                        this.newEvent.address = data.results[0].formatted_address;
                    }
                })
            },

            addSettings() {
                if (!this.startDate) {
                    this.startDate = moment().format('L');
                }

                this.newEvent.settings = [
                    { key: window.Laravel.settings.events.start_day, value: this.startDate },
                    { key: window.Laravel.settings.events.end_day, value: this.endDate }
                ]
            },

            showSuccess(file, response) {
                this.newEvent.files.push(response)
            },

            deleteFile(file, error, xhr) {
                let index = this.newEvent.files.indexOf(file.xhr.response)
                let url = this.newEvent.files.splice(index, 1)
                del(`file/delete/${url}`)
            },

            createEvent() {
                this.$validator.validateAll().then((result) => {

                    if (!this.flag || this.hasErrorDonation()) {
                        return
                    }

                    this.newEvent.campaign_id = this.$route.params.campaign_id
                    this.getDonation()
                    this.addSettings()
                    post('event/create', this.newEvent)
                        .then(res => {
                            noty({
                                text: this.$i18n.t('messages.create_success'),
                                force: false,
                                container: false,
                                type: 'success'
                            })
                            this.$route.router.go(`/campaign/${this.$route.params.campaign_id}`)
                        })
                        .catch(err => {
                            noty({
                                text: this.$i18n.t('messages.create_fail'),
                                type: 'error',
                                force: false,
                                container: false
                            })
                        })
                })
                .catch(() => {})
            },

            hasErrorDonation() {
                let errorDonations = []

                for(let index in this.errorBags) {
                    // Triger validation children donation
                    this.errorBags[index].validateAll().catch(() => {})
                    errorDonations.push(this.errorBags[index].getErrors())
                }

                return !errorDonations.every(item => !item.count())
            }
        },

        components: {
            Donations,
            Dropzone,
            SettingDate
        }
    }
</script>

<style type="text/css">
    .create-event {
        width: 80%!important;
    }
    #delete-donation:hover {
        color: #ff5e3a;
        cursor: pointer;
    }
    #add-donation:hover {
        color: #08ddc1;
        cursor: pointer;
    }
    .store-icon {
        padding-left: 5px;
        margin-bottom: 25px;
    }
    .icon-donation {
        font-size: 1.5em!important;
        padding-left: 5px;
    }
    #delete-donation {
        margin-top: 1em;
    }
    .visible:hover {
        cursor: not-allowed!important;
    }
</style>
