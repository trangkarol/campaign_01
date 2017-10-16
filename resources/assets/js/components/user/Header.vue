<template>
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="ui-block">
                    <div class="top-header">
                        <div :class="{ 'top-header-thumb': true, clickable: authUser.id == user.id }" @click="authUser.id == user.id ? handleShow('header', true) : null">
                            <img :src="user.default_header" :alt="user.name">
                        </div>
                        <div class="profile-section">
                            <div class="row">
                                <div class="col-lg-5 col-md-5 ">
                                    <ul class="profile-menu">
                                        <li>
                                            <router-link :to="{ name: 'user.timeline', params: { slug: user.slug }}">{{ $t('campaigns.timeline') }}</router-link>
                                        </li>
                                        <li>
                                            <router-link :to="{ name: 'user.friends', params: { slug: user.slug }}">{{ $t('user.friend.friends') }}</router-link>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-lg-5 offset-lg-2 col-md-5 offset-md-2">
                                    <ul class="profile-menu">
                                        <li>
                                            <router-link :to="{ name: 'user.photo', params: { slug: user.slug }}">{{ $t('user.friend.photo') }}</router-link>
                                        </li>
                                        <li>
                                            <router-link :to="{ name: 'user.list_campaign_close', params: { slug: user.slug }}">
                                                {{ $t('user.label.list_campaign_close') }}
                                            </router-link>
                                        </li>
                                        <li>
                                            <div class="more">
                                                <svg class="olymp-three-dots-icon">
                                                    <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                                </svg>
                                                <ul class="more-dropdown more-with-triangle">
                                                    <li>
                                                        <router-link :to="{ name: 'user.campaign', params: { slug: user.slug, path: 'following-campaign' }}">
                                                            {{ $t('user.label.following_campaign') }}
                                                        </router-link>
                                                    </li>
                                                    <li>
                                                        <router-link :to="{ name: 'user.campaign', params: { slug: user.slug, path: 'joined-campaign' }}">
                                                            {{ $t('user.label.joined_campaign') }}
                                                        </router-link>
                                                    </li>
                                                    <li>
                                                        <router-link :to="{ name: 'user.campaign', params: { slug: user.slug, path: 'owned-campaign' }}">
                                                            {{ $t('user.label.owned_campaign') }}
                                                        </router-link>
                                                    </li>
                                                    <li>
                                                        <router-link :to="{ name: 'campaign.create'}">
                                                            {{ $t('campaigns.create_campaign') }}
                                                        </router-link>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="control-block-button">
                                <div v-show="user.id != authUser.id" style="all:unset;">
                                    <a href="#"
                                        class="btn btn-control bg-blue"
                                        @click.prevent="unfriend(user.id)"
                                        v-if="user.is_friend">
                                        <svg class="olymp-happy-face-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-happy-face-icon"></use>
                                        </svg>
                                    </a>
                                    <a href="#"
                                        class="btn btn-control bg-blue"
                                        @click.prevent="sendRequest(user.id)"
                                        v-if="user.has_pending_request">
                                        <svg class="olymp-share-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use>
                                        </svg>
                                    </a>
                                    <a href="#"
                                        v-if="user.has_send_request"
                                        @click.prevent="acceptRequest(user.id)"
                                        class="btn btn-control bg-blue">
                                        <svg class="olymp-check-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-check-icon"></use>
                                        </svg>
                                    </a>
                                    <a href="#"
                                        v-if="user.has_send_request"
                                        @click.prevent="denyRequest(user.id)"
                                        class="btn btn-control bg-secondary">
                                        <svg class="olymp-close-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-close-icon"></use>
                                        </svg>
                                    </a>
                                    <a href="#"
                                        class="btn btn-control bg-blue"
                                        @click.prevent="sendRequest(user.id)"
                                        v-if="!user.is_friend && !user.has_pending_request && !user.has_send_request">
                                        <svg class="olymp-plus-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-plus-icon"></use>
                                        </svg>
                                    </a>
                                </div>
                                <a href="javascript:void(0)" @click="addChatComponent" class="btn btn-control bg-purple"
                                    v-if="user.id != authUser.id">
                                    <svg class="olymp-chat---messages-icon">
                                        <use xlink:href="/frontend/icons/icons.svg#olymp-chat---messages-icon"></use>
                                    </svg>
                                </a>
                                <div class="btn btn-control bg-primary more" v-else>
                                    <svg class="olymp-settings-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-settings-icon"></use></svg>
                                    <ul class="more-dropdown more-with-triangle triangle-bottom-right">
                                        <li>
                                            <a @click.prevent="handleShow('avatar', true)">{{ $t('user.upload.update_avatar') }}</a>
                                        </li>
                                        <li>
                                            <a @click.prevent="handleShow('header', true)">{{ $t('user.upload.update_header_photo') }}</a>
                                        </li>
                                        <li>
                                            <router-link :to="{ name: 'setting.profile' }">{{ $t('user.label.account_setting') }}</router-link>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="top-header-author">
                            <a class="author-thumb"
                                :style="`background-image: url('${user.image_small}')`"
                                :class="{ clickable: authUser.id == user.id }"
                                @click.prevent="authUser.id == user.id ? handleShow('avatar', true) : null">
                            </a>
                            <div class="author-content">
                                <a class="h4 author-name">{{ user.name }}</a>
                                <div class="country">{{ user.address }}</div>
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
        <image-modal :show.sync="show">
            <template slot="header">{{ modalTitle }}</template>
            <a class="upload-photo-item" @click.prevent="type == 'avatar' ? showUpload('#url_file') : showUpload('#head_photo')">
                <svg class="olymp-computer-icon">
                    <use xlink:href="/frontend/icons/icons.svg#olymp-computer-icon"></use>
                </svg>
                <h6>{{ $t('user.upload.upload_photo') }}</h6>
                <span>{{ $t('user.upload.upload_photo_desc') }}</span><br/>
                <span v-show="type == 'avatar' ? errors.has('url_file') : errors.has('head_photo')" class="material-input text-danger">
                    {{ type == 'avatar' ? errors.first('url_file') : errors.first('head_photo') }}
                </span>
            </a>

            <a href="#" class="upload-photo-item" @click.prevent="handleShowAll(true)">
                <svg class="olymp-photos-icon">
                    <use xlink:href="/frontend/icons/icons.svg#olymp-photos-icon"></use>
                </svg>
                <h6>{{ $t('user.upload.choose_from_my_photo') }}</h6>
                <span>{{ $t('user.upload.choose_from_my_photo_desc') }}</span>
            </a>
        </image-modal>

        <image-modal :show.sync="showAll">
            <template slot="header">{{ $t('user.upload.select_photo') }}</template>
            <div class="ui-block-content">
                <div class="tab-pane active" id="home" role="tabpanel" aria-expanded="true" v-if="listPhoto.length">
                    <div class="choose-photo-item" data-mh="choose-item" v-for="image in listPhoto">
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
                    @click.prevent="handleShowAll(false)">
                    {{ $t('user.upload.cancel') }}
                </a>
                <a href="#"
                    class="btn btn-primary btn-lg btn--half-width"
                    @click.prevent="handleUpdate()"
                    v-if="listPhoto.length && selectImage.url_file">
                    {{ $t('user.upload.confirm') }}
                </a>
            </div>
        </image-modal>
    </div>
</template>
<script>
    import { mapState, mapActions, mapMutations } from 'vuex'
    import ImageModal from '../libs/SelectImageModal.vue'
    import { get, post } from '../../helpers/api'
    import Noty from 'noty'
    import { EventBus } from '../../EventBus.js'
    export default {
        data() {
            return {
                show: false,
                showAll: false,
                type: null,
                selectImage: {
                    url_file: null
                },
                listPhoto: [],
                photoPage: 0,
                isLastPhotoPage: false
            }
        },
        props: {
            user: Object
        },
        methods: {
            ...mapActions('auth', [
                'updateHeaderPhoto',
                'changeAvatar',
                'uploadImage'
            ]),
            handleShow(type, status) {
                this.show = status

                if (type)
                    this.type = type
                else
                    this.type = ''
            },
            handleShowAll(status) {
                this.showAll = status

                if (!this.listPhoto.length) {
                    this.photoPage = 0
                    this.getUserPhotos()
                }
            },
            getUserPhotos() {
                this.photoPage++
                get(`user/${this.pageId || this.authUser.id}/get-photos-user?page=${this.photoPage}`)
                    .then(res => {
                        this.totalPage = res.data.photos.last_page
                        this.listPhoto = this.listPhoto.concat(res.data.photos.data)

                        if (this.photoPage == res.data.photos.last_page)
                            this.isLastPhotoPage = true
                    })
            },
            handleUpdate() {
                if (this.type == 'header')
                    this.updateHeaderPhoto(this.imageSelect)
                else
                    this.changeAvatar(this.imageSelect)
                this.handleShowAll(false)
                this.handleShow(null, false)
            },
            filesChange(event, path) {
                this.$validator.validateAll().then(() => {
                    this.$Progress.start()
                    let formData = new FormData()
                    $.each(event.currentTarget.files, (i, file) => {
                        formData.append('uploads[]', file)
                    })
                    this.uploadImage({ path, formData })
                        .then(res => {
                            $.material.init()
                            EventBus.$emit('photo')
                            this.photoPage = 0
                            this.isLastPhotoPage = false
                            this.listPhoto = []
                            this.handleShowAll(true)
                            this.$Progress.finish()
                        })
                        .catch(err => {
                            this.$Progress.fail()
                        })
                })
            },
            showUpload(el) {
                $(el).click()
            },
            ...mapMutations('user', {
                updateCurrentPageUser: 'UPDATE_CURRENT_PAGE_USER'
            }),
            // friends
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
                            this.$socket.emit('unfriend', {
                                userId: this.authUser.id,
                                unfriendId: id
                            })
                        })
                })
            },
            // send request or cancel request
            sendRequest(id) {
                post(`send-friend-request/${id}`)
                    .then(res => {
                        if (res.data.type) {
                            this.$socket.emit('sendRequest', {
                                userId: this.authUser.id,
                                acceptId: id
                            })
                        } else {
                            this.$socket.emit('rejectRequest', {
                                userId: this.authUser.id,
                                rejectId: id,
                                index: -1
                            })
                        }
                    })
            },
            acceptRequest(id) {
                post(`accept-friend-requset/${id}`, { id: null })
                    .then(() => {
                        this.$socket.emit('acceptRequest', {
                            userId: this.user.id,
                            acceptId: this.authUser.id,
                            avatar: this.authUser.image_thumbnail,
                            name: this.authUser.name,
                            receiveAvatar: this.user.image_thumbnail,
                            receiveName: this.user.name
                        })
                    })
            },
            denyRequest(id) {
                this.modal(`<h4 class="text-center">${this.$t('user.quote.deny_request_msg')}</h4>`, () => {
                    post(`deny-friend-request/${id}`)
                        .then(() => {
                            this.$socket.emit('rejectRequest', {
                                userId: this.authUser.id,
                                rejectId: id,
                                index: -1
                            })
                        })
                })
            },
            changeState(hasPendingRequest, hasSendRequest, isFriend) {
                let user = _.cloneDeep(this.user)
                user.has_pending_request = hasPendingRequest
                user.is_friend = isFriend
                user.has_send_request = hasSendRequest
                this.updateCurrentPageUser(user)
            },
            addChatComponent() {
                EventBus.$emit('addChatComponent', {
                    id: this.user.id,
                    name: this.user.name,
                    singleChat: true,
                    slug: this.user.slug
                })
            }
        },
        computed: {
            ...mapState('auth', {
                authUser: 'user'
            }),
            modalTitle() {
                if (this.type == 'avatar')
                    return this.$t('user.upload.update_avatar')
                else if (this.type == 'header')
                    return this.$t('user.upload.update_header_photo')
                else
                    return
            },
            imageSelect() {
                let img = this.selectImage.url_file.substring(8)
                return {
                    url_file: img.substring(img.lastIndexOf('?'), -255)
                }
            }
        },
        mounted() {
            $('.tab-content').scroll((e) => {
                const el = $(e.currentTarget)

                if (el.scrollTop() + el.innerHeight() + 1 >= el[0].scrollHeight && !this.isLastPhotoPage)
                    this.getUserPhotos()
            })
        },
        updated() {
            $.material.init()
        },
        components: {
            ImageModal
        },
        // socket
        sockets: {
            acceptRequestSuccess: function (data) {
                if ((data.data.acceptId == this.authUser.id && data.data.userId == this.user.id)
                    || (data.data.acceptId == this.user.id && data.data.userId == this.authUser.id)
                ) {
                    this.changeState(false, false, 1)
                }
            },
            rejectRequestSuccess: function (data) {
                if ((data.data.rejectId == this.authUser.id && data.data.userId == this.user.id)
                    || (data.data.rejectId == this.user.id && data.data.userId == this.authUser.id)
                ) {
                    this.changeState(false, false, 0)
                }
            },
            sendRequestSuccess: function (data) {
                if (this.authUser.id == data.data.userId && this.user.id == data.data.acceptId) {
                    this.changeState(true, false, 0)
                }

                if (data.data.acceptId == this.authUser.id && data.data.userId == this.user.id) {
                    this.changeState(false, true, 0)
                }
            },
            unfriendSuccess: function (data) {
                if ((data.data.unfriendId == this.authUser.id && data.data.userId == this.user.id)
                    || (data.data.unfriendId == this.user.id && data.data.userId == this.authUser.id)
                ) {
                    this.changeState(false, false, false)
                }
            }
        }
    }
</script>
<style lang="scss" scoped>
    .author-thumb {
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
    }
    .clickable:hover {
        cursor: pointer;
    }
    .top-header-thumb {
        >img {
            max-height: 500px;
            object-fit: cover;
        }
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
