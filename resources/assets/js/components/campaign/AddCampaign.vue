<template lang="html">
    <div class="container">
        <div id="create-campagin">
            <div id="container-campaign" class="modal-dialog ui-block window-popup create-event">
                <div class="ui-block-title">
                    <h6 class="title">{{ $t('campaigns.create_campaign') }}</h6>
                </div>
                <form class="content" @submit.prevent="handleCreateCampagin">
                    <div class="ui-block-content">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group label-floating">
                                    <label class="control-label">{{ $t('campaigns.title') }}</label>
                                    <input id="title"
                                        name="title"
                                        class="form-control"
                                        type="text"
                                        v-model="campaign.title"
                                        v-validate="'required|max:255'">
                                    <span class="material-input"></span>
                                    <span v-show="errors.has('title')" class="material-input text-danger">
                                        {{ errors.first('title') }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group label-floating">
                                    <label class="control-label">{{ $t('campaigns.hashtag') }}</label>
                                    <input id="hashtag"
                                        name="hashtag"
                                        class="form-control"
                                        type="text"
                                        v-model="campaign.hashtag"
                                        v-validate="'required|max:255|unique:campaigns,hashtag'">
                                    <span class="material-input"></span>
                                    <span v-show="errors.has('hashtag')" class="material-input text-danger">
                                        {{ errors.first('hashtag') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group label-floating">
                            <multiselect
                                v-model="campaign.tags"
                                :tag-placeholder="$t('campaigns.add_tags')"
                                :placeholder="$t('campaigns.tags')"
                                label="name"
                                track-by="id"
                                :options="tags"
                                :multiple="true"
                                :taggable="true"
                                @tag="addTags">
                            </multiselect>
                        </div>

                        <div class="form-group label-floating">
                            <label class="control-label">{{ $t('campaigns.location') }}</label>
                            <gmap-autocomplete
                                id="address"
                                name="address"
                                :value="campaign.address"
                                class="form-control"
                                @place_changed="setPlace"
                                data-vv-name="campaign.address"
                                v-validate="'max:255'">
                            </gmap-autocomplete>
                            <span class="material-input"></span>
                            <span v-show="errors.has('address')" class="material-input text-danger">
                                {{ errors.first('address') }}
                            </span>
                        </div>

                        <div id="form-group label-floating">
                            <gmap-map id="map" :center="center" :zoom="zoom" v-if="showMap">
                                <gmap-marker
                                    :position="center"
                                    :clickable="true"
                                    :draggable="true"
                                    @click="center"
                                    @dragend="updatePosition($event)">
                                </gmap-marker>
                            </gmap-map>
                        </div>

                        <div class="form-group label-floating"></div>
                        <div class="ui-block-title">
                            <h6 class="title">{{ $t('campaigns.settings') }}</h6>
                        </div>

                        <div class="row setting">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 form-group label-floating is-select is-empty">
                                <label class="control-label">{{ $t('campaigns.status') }}</label>
                                <div class="btn-group bootstrap-select form-control">
                                    <div class="dropdown-menu open" role="combobox">
                                        <ul class="dropdown-menu inner" role="listbox" aria-expanded="false">
                                            <li data-original-index="0" class="selected"
                                                v-for="name in status">
                                                <a tabindex="0"
                                                    class=" dropdown-item"
                                                    data-tokens="null"
                                                    role="option"
                                                    aria-disabled="false"
                                                    aria-selected="true">
                                                    <span class="text">{{ name.name }}</span>
                                                    <span class="glyphicon glyphicon-ok check-mark"></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <select ref="selectpicker"
                                        class="selectpicker form-control"
                                        tabindex="-98"
                                        v-model="feature">
                                        <option v-for="name in status" :value="{ value: name.value }">
                                            {{ name.name }}
                                        </option>
                                    </select>
                                </div>
                                <span class="material-input"></span>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group label-floating">
                                    <label class="control-label">{{ $t('campaigns.limit') }}</label>
                                    <input id="limit"
                                        name="limit"
                                        class="form-control"
                                        type="text"
                                        v-model="limit"
                                        v-validate="'numeric'">
                                    <span class="material-input"></span>
                                    <span v-show="errors.has('limit')" class="material-input text-danger">
                                        {{ errors.first('limit') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <setting-date
                            :startDay.sync="startDay"
                            :endDay.sync="endDay"
                            :flag.sync="flag">
                        </setting-date>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <button type="button"
                                    class="btn btn-blue"
                                    @click="openFile($event)">
                                    {{ $t('campaigns.upload_image') }}
                                </button>
                                <button type="button"
                                    class="btn btn-primary"
                                    @click="cancelFile($event)">
                                    {{ $t('campaigns.cancel_image') }}
                                </button>
                                <input id="uploadFile"
                                    type="file"
                                    name="file"
                                    @change="uploadImage($event)"
                                    v-validate="'required|image|size:1024'">
                                </br>
                                <span v-show="errors.has('file')" class="material-input text-danger">
                                    {{ errors.first('file') }}
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <img :src="campaign.media" alt="image" id="img" v-if="campaign.media"/>
                            </div>
                        </div>
                        <div class="form-group label-floating"></div>
                        <div class="form-group label-floating">
                            <label class="control-label">{{ $t('campaigns.description') }}</label>
                            <quill-editor name="description"
                                data-vv-name="description"
                                id="description"
                                class="form-control"
                                v-model="campaign.description"
                                v-validate="'required'">
                            </quill-editor>
                            <span v-show="errors.has('description')" class="material-input text-danger">
                                {{ errors.first('description') }}
                            </span>
                        </div>
                        <button type="submit" class="btn btn-breez btn-lg full-width">
                            {{ $t('campaigns.create_campaign') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import DatePicker from '../libs/DatePicker.vue'
import { post, get } from '../../helpers/api'
import { storeCampaign, tags } from '../../router/router'
import Multiselect from 'vue-multiselect'
import noty from '../../helpers/noty'
import SettingDate from '../libs/SettingDate.vue'
import { quillEditor } from 'vue-quill-editor'
import * as VueGoogleMaps from 'vue2-google-maps'
import { config } from '../../config'

export default {
    data: () => ({
        campaign: {
            title: '',
            hashtag: '',
            description: '',
            longitude: '',
            latitude: '',
            tags: [],
            settings: [],
            media: '',
            address: ' '
        },
        tags: [],
        feature: '',
        limit: '',
        startDay: '',
        endDay: '',
        flag: '',
        content: null,
        validator: null,
        showMap: false,
        zoom: config.zoom,
        latLng: { lat: 0, lng: 0 },
        center: { lat: 0, lng: 0 }
    }),
    created () {
        this.getTags()
    },
    mounted() {
        $(this.$refs.selectpicker).selectpicker()
    },
    beforeDestroy() {
        $(this.$refs.selectpicker).selectpicker('destroy')
    },
    computed: {
        status() {
            return [
                { name: this.$i18n.t('campaigns.public'), value: window.Laravel.settings.value.status.public },
                { name: this.$i18n.t('campaigns.private'), value: window.Laravel.settings.value.status.private }
            ]
        }
    },
    methods: {
        handleCreateCampagin() {
            this.$validator.validateAll().then((result) => {
                // add settings for campaign
                this.addSettings(window.Laravel.settings.campaigns.status, this.feature.value)
                this.addSettings(window.Laravel.settings.campaigns.limit, this.limit)
                this.addSettings(window.Laravel.settings.campaigns.start_day, this.startDay)
                this.addSettings(window.Laravel.settings.campaigns.end_day, this.endDay)

                if (!this.flag) {
                    return
                }

                post(storeCampaign, this.campaign)
                    .then(res => {
                        const message = this.$i18n.t('messages.create_success')
                        noty({ text: message, container: false, force: true, type: 'success'})
                    })
                    .catch(err => {
                        const message = this.$i18n.t('messages.create_fail')
                        noty({ text: message, container: false, force: true})
                    })
            })
        },
        addTags(newTag) {
            let tag = {
                name: newTag,
                id: null
            }

            this.tags.push(tag)
            this.campaign.tags.push(tag)
        },
        getTags() {
            get(tags)
                .then(res => {
                    this.tags = res.data.tags.map(function(value, key) {
                        return {
                            name: value.name,
                            id: value.id
                        }
                    })
                })
                .catch(err => {
                    this.tags.push(this.$i18n.t('messages.empty'))
                })
        },
        addSettings(key, value) {
            if (key == window.Laravel.settings.campaigns.status && value == null) {
                value = window.Laravel.settings.value.status.public
            }

            let flag = false

            this.campaign.settings.map(function (setting) {
                if (setting.key == key) {
                    setting.value = value
                    flag = true
                }
            })

            if (!flag) {
                let setting = {
                    key: key,
                    value: value
                }

                this.campaign.settings.push(setting)
            }
        },
        uploadImage(event) {
            var file = event.target.files[0]
            var type = ["image/gif", "image/jpeg", "image/png", "image/jpg"];

            if ($.inArray(file["type"], type) < 0) {
                this.campaign.media = ''
                document.getElementById('uploadFile').value = ''

                return
            }

            var fileReader = new FileReader()

            fileReader.onload = (event) => {
                this.campaign.media = event.target.result
            }

            fileReader.readAsDataURL(file)

        },
        openFile(event) {
            document.getElementById('uploadFile').click()
        },
        cancelFile(event) {
            document.getElementById('uploadFile').value = ''
            this.campaign.media = ''
        },
        setPlace(place) {
            this.latLng = {
                lat: place.geometry.location.lat(),
                lng: place.geometry.location.lng()
            }

            this.campaign.address = place.formatted_address
            this.campaign.latitude = place.geometry.location.lat()
            this.campaign.longitude = place.geometry.location.lat()
            this.center= {
                lat: place.geometry.location.lat(),
                lng: place.geometry.location.lng()
            }

            if (!this.showMap) {
                this.showMap = true;
            }
        },
        updatePosition(event) {
            this.latLng = {
                lat: event.latLng.lat(),
                lng: event.latLng.lng()
            }

            this.campaign.latitude = event.latLng.lat()
            this.campaign.longitude = event.latLng.lng()

            $.ajax({
                url: `https://maps.googleapis.com/maps/api/geocode/json?latlng=
                    ${event.latLng.lat()},
                    ${event.latLng.lng()}
                    &key=${config.keyMap}`,
                success: data => {
                    this.campaign.address = data.results[0].formatted_address
                }
            })
        }
    },
    components: {
        DatePicker,
        Multiselect,
        SettingDate,
        quillEditor
    }
}
</script>

<style lang="scss">
#create-campaign {
    width: 100% !important;
    display: block;
}
#container-campaign {
    width: 100% !important;
}

.setting {
    margin-top: 20px;
}

.dropdown-menu .open {
    max-height: 189.438px;
    overflow: hidden;
    min-height: 0px;
}

.dropdown-menu .inner {
     max-height: 171.438px;
     overflow-y: auto;
     min-height: 0px;
}

.multiselect__tags {
    min-height: 54px !important;

}

.multiselect__input {
    padding: initial  !important;
}

#img {
    border: 5px solid #dedede;
    max-width: 100%;
    height: 330px;
    margin: 7px 0px;
}

#map {
    width: 100%;
    height: 400px;
}

#uploadFile {
    display: none;
}
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
