<template lang="html">
    <div class="container">
        <div id="create-campagin">
            <div id="container-campaign" class="modal-dialog ui-block window-popup create-event">
                <div class="ui-block-title">
                    <h6 class="title">{{ $t('campaigns.update_campaign') }}</h6>
                </div>
                <form class="content" @submit.prevent="updateCampagin">
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
                                        v-validate="'required|max:255|unique:campaigns,hashtag,' + campaign.id">
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
                                ref="elSearch"
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
                            <gmap-map id="map" :center="center" :zoom="zoom" ref="elMap">
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
                                        v-if="feature"
                                        class="selectpicker form-control"
                                        tabindex="-98"
                                        v-model="feature">
                                        <option v-for="name in status" :value="name.value">
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
                                        v-validate="'numeric'"
                                        @change="checkLimit(limit)">
                                    <span class="material-input"></span>
                                    <span v-show="errors.has('limit')" class="material-input text-danger">
                                        {{ errors.first('limit') }}
                                    </span>
                                    <span v-show="totalError" class="material-input text-danger">
                                        {{ totalError }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <setting-date v-if="showSettingDay"
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
                                    v-show="campaign.media"
                                    class="btn btn-primary"
                                    @click="cancelFile($event)">
                                    {{ $t('campaigns.cancel_image') }}
                                </button>
                                <input id="uploadFile"
                                    type="file"
                                    name="file"
                                    @change="uploadImage($event)"
                                    v-validate="typeof campaign.media != 'string'
                                        ? 'required|image|size:1024'
                                        : ''">
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
                            <quill-editor name="description"
                                data-vv-name="description"
                                id="description"
                                ref="description"
                                v-model="campaign.description"
                                :class="{ fullscreen: toggleFullscreen }"
                                :options="editorOption"
                                v-validate="'required'">
                            </quill-editor>
                            <span v-show="errors.has('description')" class="material-input text-danger">
                                {{ errors.first('description') }}
                            </span>
                        </div>

                        <button type="submit" class="btn btn-breez btn-lg full-width">
                            {{ $t('campaigns.update_campaign') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <upload-quill
            :uploadVisible.sync="uploadVisible"
            :imageInsert="imageInsert"
            @insertImage="insertImageToContent">
        </upload-quill>
    </div>
</template>

<script>
import DatePicker from '../../libs/DatePicker.vue'
import { post, get, put } from '../../../helpers/api'
import { storeCampaign, tags } from '../../../router/router'
import Multiselect from 'vue-multiselect'
import noty from '../../../helpers/noty'
import SettingDate from '../../libs/SettingDate.vue'
import * as VueGoogleMaps from 'vue2-google-maps'
import { config, editorOption } from '../../../config'
import uploadedImage from '../../../helpers/mixin/uploadedImage'
import { mapState } from 'vuex'
import searchMap from '../../../helpers/mixin/searchMap'

export default {
    data: function () {
        return {
            campaign: {
                id: '',
                title: ' ',
                hashtag: ' ',
                description: ' ',
                longitude: ' ',
                latitude: ' ',
                tags: [],
                settings: [],
                media: '',
                address: ' '
            },
            tags: [],
            feature: null,
            limit: ' ',
            startDay: '',
            endDay: '',
            flag: '',
            content: null,
            zoom: config.zoom,
            latLng: { lat: 0, lng: 0 },
            center: { lat: 0, lng: 0 },
            showSettingDay: false,
            status: [
                { name: this.$i18n.t('campaigns.public'), value: window.Laravel.settings.value.status.public },
                { name: this.$i18n.t('campaigns.private'), value: window.Laravel.settings.value.status.private }
            ],
            totalUser: 0,
            totalError: ''
        }
    },
    mixins: [uploadedImage, searchMap],
    created () {
        this.getTags()
        this.getCampaign(this.pageId)
    },
    watch: {
        feature(value) {
            this.feature = value

            return value
        }
    },
    mounted() {
        $(this.$refs.selectpicker).selectpicker()
    },
    beforeDestroy() {
        $(this.$refs.selectpicker).selectpicker('destroy')
    },
    computed: {
        ...mapState('auth', {
            user: state => state.user
        })
    },
    methods: {
        getCampaign(id) {
            get(`campaign/${id}/edit`)
                .then(res => {
                    this.$Progress.start()

                    if (!res.data.http_status.code == 200) {
                        const message = this.$i18n.t('messages.not_permission')
                        this.$Progress.finish()
                        noty({ text: message, container: false, force: true})
                        this.$router.push({ name: 'user', params: { id: this.user.id } })
                    }

                    this.totalUser = res.data.totalUser
                    let c = res.data.campaign
                    this.campaign.id = c.id
                    this.campaign.address = c.address
                    this.campaign.title = c.title
                    this.campaign.hashtag = c.hashtag
                    this.campaign.description = c.description
                    this.campaign.latitude = parseFloat(c.latitude)
                    this.campaign.longitude = parseFloat(c.longitude)
                    this.campaign.tags = c.tags
                    this.campaign.media = c.media[0].image_default
                    this.center.lng = parseFloat(c.longitude)
                    this.center.lat = parseFloat(c.latitude)

                    for (var index = 0; index < c.settings.length; index++) {
                        this.addSettings(c.settings[index].key, c.settings[index].value)

                        switch(c.settings[index].key) {
                            case window.Laravel.settings.campaigns.status:
                                this.feature = c.settings[index].value
                                break
                            case window.Laravel.settings.campaigns.limit:
                                this.limit = c.settings[index].value
                                break
                            case window.Laravel.settings.campaigns.start_day:
                                this.flag = true
                                this.startDay = c.settings[index].value
                                break
                            case window.Laravel.settings.campaigns.end_day:
                                this.flag = true
                                this.endDay = c.settings[index].value != String(null)
                                    ? c.settings[index].value
                                    : null
                                break
                        }
                    }

                    this.showSettingDay = true
                })
                .catch(err => {
                    const message = this.$i18n.t('messages.error')
                    this.$Progress.finish()
                    noty({ text: message, container: false, force: true})
                    this.$router.push({ name: 'user', params: { id: this.user.id } })
                })
        },
        updateCampagin() {
            this.$validator.validateAll().then((result) => {
                // add settings for campaign
                this.addSettings(window.Laravel.settings.campaigns.status, this.feature)
                this.addSettings(window.Laravel.settings.campaigns.limit, this.limit)
                this.addSettings(window.Laravel.settings.campaigns.start_day, this.startDay)
                this.addSettings(window.Laravel.settings.campaigns.end_day, this.endDay)

                if (!this.flag) {
                    return
                }

                this.$Progress.start()
                put(`campaign/${this.campaign.id}`, this.campaign)
                    .then(res => {
                        const message = this.$i18n.t('messages.update_success')
                        this.$Progress.finish()
                        noty({ text: message, container: false, force: true, type: 'success'})
                        this.$router.push({ name: 'campaign.timeline', params: { id: res.data.campaign.id }})
                    })
                    .catch(err => {
                        const message = this.$i18n.t('messages.update_fail')
                        this.$Progress.fail()
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
                    setting.value = String(value)
                    flag = true
                }
            })

            if (!flag) {
                let setting = {
                    key: key,
                    value: String(value)
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
            this.setLocation(this.campaign, place)
        },

        updatePosition(event) {
            const latLng = event.latLng.toJSON()
            this.setGeocoder(this.campaign, latLng)
        },
        check(value) {
            return value == 1 ? 'selected' : ''
        },
        checkLimit(value) {
            if (value && Number(value) < this.totalUser) {
                this.totalError = this.$i18n.t('validations.setting_larger') + this.totalUser
                this.flag = false

                return
            }

            this.flag = true
            this.totalError = ''
        }
    },
    components: {
        DatePicker,
        Multiselect,
        SettingDate
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
