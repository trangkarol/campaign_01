<template>
    <div>
        <!-- Top Header -->
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="ui-block">
                        <div class="top-header">
                            <div class="top-header-thumb clickable" @click="showHeader = true" v-if="authUser.id == currentPageUser.id">
                                <img :src="authUser.default_header" alt="nature">
                            </div>
                            <div class="top-header-thumb" v-else>
                                <img :src="currentPageUser.default_header" alt="nature">
                            </div>
                            <div class="profile-section">
                                <div class="row">
                                    <div class="col-lg-5 col-md-5 ">
                                        <ul class="profile-menu">
                                            <li>
                                                <router-link :to="{ name: 'user.timeline', params: { userId }}">{{ $t('campaigns.timeline') }}</router-link>
                                            </li>
                                            <li>
                                                <router-link :to="{ name: 'user.about', params: { userId }}">About</router-link>
                                            </li>
                                            <li>
                                                <router-link :to="{ name: 'user.friends', params: { userId }}">{{ $t('user.friend.friends') }}</router-link>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-lg-5 offset-lg-2 col-md-5 offset-md-2">
                                        <ul class="profile-menu">
                                            <li>
                                                <router-link :to="{ name: 'user.photo', params: { userId }}">{{ $t('user.friend.photo') }}</router-link>
                                            </li>
                                            <li>
                                                <router-link :to="{ name: 'user.video', params: { userId }}">{{ $t('user.friend.video') }}</router-link>
                                            </li>
                                            <li>
                                                <div class="more">
                                                    <svg class="olymp-three-dots-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use></svg>
                                                    <ul class="more-dropdown more-with-triangle">
                                                        <li>
                                                            <a href="#">Report Profile</a>
                                                        </li>
                                                        <li>
                                                            <a href="#">Block Profile</a>
                                                        </li>
                                                        <li>
                                                            <router-link :to="{ name: 'user.campaign', params: { id: userId, path: 'following-campaign' }}">{{ $t('user.label.following_campaign') }}</router-link>

                                                        </li>
                                                        <li>
                                                            <router-link :to="{ name: 'user.campaign', params: { id: userId, path: 'joined-campaign' }}">{{ $t('user.label.joined_campaign') }}</router-link>
                                                        </li>
                                                        <li>
                                                            <router-link :to="{ name: 'user.campaign', params: { id: userId, path: 'owned-campaign' }}">{{ $t('user.label.owned_campaign') }}</router-link>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="control-block-button">
                                    <div v-show="currentPageUser.id !== authUser.id" style="all:unset;">
                                        <a href="#"
                                            class="btn btn-control bg-blue"
                                            @click.prevent="unfriend(currentPageUser.id)"
                                            v-if="currentPageUser.is_friend">
                                            <svg class="olymp-happy-face-icon">
                                                <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                                            </svg>
                                        </a>
                                        <a href="#"
                                            class="btn btn-control bg-blue"
                                            @click.prevent="sendRequest(currentPageUser.id)"
                                            v-if="currentPageUser.has_pending_request">
                                            <svg class="olymp-share-icon">
                                                <use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use>
                                            </svg>
                                        </a>
                                        <a href="#"
                                            v-if="currentPageUser.has_send_request"
                                            @click.prevent="acceptRequest(currentPageUser.id)"
                                            class="btn btn-control bg-blue">
                                            <svg class="olymp-check-icon">
                                                <use xlink:href="/frontend/icons/icons.svg#olymp-check-icon"></use>
                                            </svg>
                                        </a>
                                        <a href="#"
                                            v-if="currentPageUser.has_send_request"
                                            @click.prevent="denyRequest(currentPageUser.id)"
                                            class="btn btn-control bg-secondary">
                                            <svg class="olymp-close-icon">
                                                <use xlink:href="/frontend/icons/icons.svg#olymp-close-icon"></use>
                                            </svg>
                                        </a>
                                        <a href="#"
                                            class="btn btn-control bg-blue"
                                            @click.prevent="sendRequest(currentPageUser.id)"
                                            v-if="!currentPageUser.is_friend && !currentPageUser.has_pending_request && !currentPageUser.has_send_request">
                                            <svg class="olymp-plus-icon">
                                                <use xlink:href="/frontend/icons/icons.svg#olymp-plus-icon"></use>
                                            </svg>
                                        </a>
                                    </div>
                                    <a href="#" class="btn btn-control bg-purple" v-show="currentPageUser.id != authUser.id">
                                        <svg class="olymp-chat---messages-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-chat---messages-icon"></use></svg>
                                    </a>
                                    <div class="btn btn-control bg-primary more" v-show="authUser.id == currentPageUser.id">
                                        <svg class="olymp-settings-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-settings-icon"></use></svg>
                                        <ul class="more-dropdown more-with-triangle triangle-bottom-right">
                                            <li>
                                                <a href="#" @click.prevent="showAvatar = true">{{ $t('user.upload.update_avatar') }}</a>
                                            </li>
                                            <li>
                                                <a href="#" @click.prevent="showHeader = true">{{ $t('user.upload.update_header_photo') }}</a>
                                            </li>
                                            <li>
                                                <router-link :to="{ name: 'setting.profile' }">{{ $t('user.label.account_setting') }}</router-link>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="top-header-author">
                                <a href="#" class="author-thumb">
                                    <img :src="authUser.image_small" alt="author" v-if="authUser.id == currentPageUser.id" @click.prevent="showAvatar = true">
                                    <img :src="currentPageUser.image_small" alt="author" v-else @click.prevent>
                                </a>
                                <div class="author-content">
                                    <a href="02-ProfilePage.html" class="h4 author-name">{{ currentPageUser.name }}</a>
                                    <div class="country">{{ currentPageUser.address }}</div>
                                </div>
                                <input
                                    type="file"
                                    name="url_file"
                                    style="display: none"
                                    id="url_file"
                                    multiple=""
                                    v-validate="'image|size:1024'"
                                    @change="filesChange($event, 'avatar')">
                                <input
                                    type="file"
                                    name="head_photo"
                                    style="display: none"
                                    id="head_photo"
                                    multiple=""
                                    v-validate="'image|size:1024'"
                                    @change="filesChange($event, 'header')">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <router-view></router-view>

        <image-modal :show.sync="showAvatar">
            <template slot="header">{{ $t('user.upload.update_avatar') }}</template>
            <a href="#" class="upload-photo-item" @click.prevent="showUpload('#url_file')">
                <svg class="olymp-computer-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-computer-icon"></use></svg>

                <h6>{{ $t('user.upload.upload_photo') }}</h6>
                <span>{{ $t('user.upload.upload_photo_desc') }}</span><br/>
                <span v-show="errors.has('url_file')" class="material-input text-danger">
                    {{ errors.first('url_file') }}
                </span>
            </a>

            <a href="#" class="upload-photo-item" @click.prevent="showAllAvatar = true">

                <svg class="olymp-photos-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-photos-icon"></use></svg>

                <h6>{{ $t('user.upload.choose_from_my_photo') }}</h6>
                <span>{{ $t('user.upload.choose_from_my_photo_desc') }}</span>
            </a>
        </image-modal>

        <image-modal :show.sync="showAllAvatar">
            <template slot="header">{{ $t('user.upload.select_photo') }}</template>
            <div class="ui-block-content">
                <div class="tab-pane active" id="home" role="tabpanel" aria-expanded="true" v-if="authUser.media.length">
                    <div class="choose-photo-item" data-mh="choose-item" v-for="image in authUser.media">
                        <div class="radio">
                            <label class="custom-radio">
                                <img :src="image.image_default" alt="photo">
                                <input type="radio" name="optionsRadios" v-model="selectImage.url_file" :value="image.image_small">
                            </label>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <p>{{ $t('user.upload.no_photo_msg') }}</p>
                </div>
            </div>
            <div class="modal-footer" slot="footer">
                <a href="#"
                    class="btn btn-secondary btn-lg btn--half-width"
                    @click.prevent="showAllAvatar = false">
                    {{ $t('user.upload.cancel') }}
                </a>
                <a href="#"
                    class="btn btn-primary btn-lg btn--half-width"
                    @click.prevent="handleUpdate('avatar ')"
                    v-if="authUser.media.length && selectImage.url_file">
                    {{ $t('user.upload.confirm') }}
                </a>
            </div>
        </image-modal>

        <image-modal :show.sync="showHeader">
            <template slot="header">{{ $t('user.upload.update_header_photo') }}</template>
            <a href="#" class="upload-photo-item" @click.prevent="showUpload('#head_photo')">
                <svg class="olymp-computer-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-computer-icon"></use></svg>

                <h6>{{ $t('user.upload.upload_photo') }}</h6>
                <span>{{ $t('user.upload.upload_photo_desc') }}</span><br/>
                <span v-show="errors.has('head_photo')" class="material-input text-danger">
                    {{ errors.first('head_photo') }}
                </span>
            </a>

            <a href="#" class="upload-photo-item" @click.prevent="showAllImage = true">

                <svg class="olymp-photos-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-photos-icon"></use></svg>

                <h6>{{ $t('user.upload.choose_from_my_photo') }}</h6>
                <span>{{ $t('user.upload.choose_from_my_photo_desc') }}</span>
            </a>
        </image-modal>
        <image-modal :show.sync="showAllImage">
            <template slot="header">{{ $t('user.upload.select_photo') }}</template>
            <div class="ui-block-content">
                <div class="tab-pane active" id="home" role="tabpanel" aria-expanded="true" v-if="authUser.media.length">
                    <div class="choose-photo-item" data-mh="choose-item" v-for="image in authUser.media">
                        <div class="radio">
                            <label class="custom-radio">
                                <img :src="image.image_default" alt="photo">
                                <input type="radio" name="optionsRadios" v-model="selectImage.url_file" :value="image.image_small">
                            </label>
                        </div>
                    </div>
                </div>
                <div v-else class="tab-pane active">
                    <p>{{ $t('user.upload.no_photo_msg') }}</p>
                </div>
            </div>
            <div class="modal-footer" slot="footer">
                <a href="#"
                    class="btn btn-secondary btn-lg btn--half-width"
                    @click.prevent="showAllImage = false">
                    {{ $t('user.upload.cancel') }}
                </a>
                <a href="#"
                    class="btn btn-primary btn-lg btn--half-width"
                    @click.prevent="handleUpdate('header')"
                    v-if="authUser.media.length && selectImage.url_file">
                    {{ $t('user.upload.confirm') }}
                </a>
            </div>
        </image-modal>
    </div>
</template>

<script>
    import noty from '../../helpers/noty'
    import { mapState, mapActions, mapMutations } from 'vuex'
    import { get, post } from '../../helpers/api'
    import ImageModal from '../libs/SelectImageModal.vue'
    import { EventBus } from '../../EventBus.js'
    import Noty from 'noty'
    import slug from '../../helpers/mixin/getFullSlug'

    export default {
        mixins: [slug],
        data: () => ({
            showAvatar: false,
            showAllAvatar: false,
            showHeader: false,
            showAllImage: false,
            selectImage: {
                url_file: ''
            },
            pageType: 'user'
        }),
        mounted() {
            $.material.init()
        },
        computed: {
            ...mapState('user',[
                'currentPageUser',
                'listActivity',
                'loading',
            ]),
            ...mapState('like',['showModal', 'listUserliked']),
            ...mapState('auth', {
                authUser: 'user'
            }),
            userId: function () {
                return this.pageId
            },
            imageSelect() {
                let img = this.selectImage.url_file.substring(8)
                return {
                    url_file: img.substring(img.lastIndexOf('?'), -255)
                }
            }
        },
        created() {
            this.getUser(this.userId)
        },
        watch: {
            // call again the method if the route changes
            $route () {
                this.getUser(this.userId)
                EventBus.$emit('redirect-page')
            }
        },
        methods: {
            ...mapActions('auth', [
                'updateHeaderPhoto',
                'changeAvatar',
                'uploadImage'
            ]),
            ...mapActions('like', ['hideUsersLiked']),
            ...mapActions('user', ['getUser']),
            ...mapMutations('user', {
                updateCurrentPageUser: 'UPDATE_CURRENT_PAGE_USER'
            }),
            showUpload(el) {
                $(el).click()
            },
            filesChange(event, path) {
                this.$validator.validateAll().then(() => {
                    let formData = new FormData()
                    $.each(event.currentTarget.files, (i, file) => {
                        formData.append('uploads[]', file)
                    })
                    this.uploadImage({ path, formData })
                        .then(res => {
                            $.material.init()
                            if (path == 'header')
                                this.showAllImage = true
                            else
                                this.showAllAvatar = true
                            EventBus.$emit('photo', 'updateListPhoto')
                        })
                        .catch(err => {})
                })
            },
            handleUpdate(type) {
                if (type == 'header')
                    this.updateHeaderPhoto(this.imageSelect)
                else
                    this.changeAvatar(this.imageSelect)
                this.showAvatar = false
                this.showAllAvatar = false
                this.showHeader = false
                this.showAllImage = false
            },
            modal(text, callback) {
                let n = new Noty({
                    text: text,
                    modal: true,
                    layout: 'center',
                    closeWith: 'button',
                    buttons: [
                        Noty.button(this.$t('events.donation.yes'), 'btn-upper btn btn-primary btn--half-width', () => {
                            callback()
                            n.close()
                        }),
                        Noty.button(this.$t('events.donation.no'), 'btn-upper btn btn-secondary btn--half-width', () => {
                            n.close()
                        })
                    ]
                }).show();
            },
            unfriend(id) {
                this.modal(`<h4 class="text-center">${this.$t('user.quote.unfriend_msg')}</h4>`, () => {
                    post(`unfriend/${id}`)
                        .then(() => {
                            let data = _.cloneDeep(this.currentPageUser)
                            data.is_friend = false
                            this.updateCurrentPageUser(data)
                        })
                })
            },
            // send request or cancel request
            sendRequest(id) {
                post(`send-friend-request/${id}`)
                    .then(() => {
                        let data = _.cloneDeep(this.currentPageUser)
                        data.has_pending_request = !data.has_pending_request
                        this.updateCurrentPageUser(data)
                    })
            },
            acceptRequest(id) {
                post(`accept-friend-requset/${id}`)
                    .then(() => {
                        let data = _.cloneDeep(this.currentPageUser)
                        data.has_send_request = false
                        data.is_friend = true
                        this.updateCurrentPageUser(data)
                    })
            },
            denyRequest(id) {
                this.modal(`<h4 class="text-center">${this.$t('user.quote.deny_request_msg')}</h4>`, () => {
                    post(`deny-friend-request/${id}`)
                        .then(() => {
                            let data = _.cloneDeep(this.currentPageUser)
                            data.has_send_request = false
                            this.updateCurrentPageUser(data)
                        })
                })
            },
        },
        components: {
            ImageModal
        }
    }
</script>

<style lang="scss" scoped>
    .author-thumb {
        >img {
            max-height: 100%;
        }
    }
    .clickable:hover {
        cursor: pointer;
    }

    .list-user-liked {
        >ul {
            border: 1px solid #e6ecf5;
            padding: 10px;
            overflow-y: auto;
            max-height: 400px;
            border-radius: 2px;
            >li {
                line-height: 45px;
                padding-left: 10px;
                font-size: 0.875rem;
                font-weight: 700;
                color: #515365;
                &:hover {
                    background: #e6ecf5;
                    border-radius: 2px;
                }
            }
        }
        img {
            width: 32px;
            height: 32px;
            border-radius: 22px;
            margin-right: 7px;
            border-radius: 15px;
        }
    }

    #create-friend-group-add-friends {
        display: block;
        background: rgba(59, 59, 60, 0.57);
    }

    .fa-heart {
        color: #ff5e3a;
        font-size: 20px;
        margin-right: -5px;
    }

    .ui-block-title {
        border-top: 0px;
    }

    .top-header-thumb {
        >img {
            max-height: 500px;
            object-fit: cover;
        }
    }
    .control-block-button
    .btn-control {
        margin-right: 0;
    }
    .btn--half-width {
        margin-left: 5px;
    }
    .control-block-button {
        .btn-control{
            .olymp-share-icon,
            .olymp-check-icon,
            .olymp-close-icon,
            .olymp-plus-icon,
            .olymp-happy-face-icon {
                width: 23px;
                height: 20px;
            }
        }
    }
</style>
