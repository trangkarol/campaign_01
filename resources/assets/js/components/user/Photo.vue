<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="ui-block responsive-flex">
                        <div class="ui-block-title">
                            <div class="h6 title">{{ $t('user.photo.gallery') + ' ' + currentPageUser.name }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="info-empty" v-if="!listPhoto.length">
            <span>
                <i class="fa fa-file-image-o" aria-hidden="true"></i>
                {{ $t('user.photo.dont_have_images') }}
            </span>
        </div>
        <div class="container" v-else>
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="tab-content ui-block box-tag-image">
                        <div id="photo-page" role="tabpanel" aria-expanded="true" class="tab-pane active">
                            <div class="photo-album-wrapper">
                                <div class="photo-item col-4-width" v-for="(image, index) in listPhoto">
                                    <div class="tag-image" :style="{ 'background-image': 'url(' + image.image_large + ')' }">
                                        <div class="overlay overlay-dark"></div>
                                        <div class="more" v-if="user.id == image.mediable_id">
                                            <svg class="olymp-three-dots-icon">
                                                <use xlink:href="/frontend/icons/icons.svg#olymp-three-dots-icon"></use>
                                            </svg>
                                            <ul class="more-dropdown">
                                                <li>
                                                    <a href="javascript:void(0)" @click="deletePhoto(image.id)">
                                                        {{ $t('user.photo.delete') }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0)" @click="showImageDetail(index)" class="full-block"></a>
                                    <div class="content">
                                        <a href="javascript:void(0)" class="h6 title">{{ $t('user.photo.header') }}</a>
                                        <time class="published">{{ image.time_ago }}</time>
                                    </div>
                                </div>
                                <div class="spinner-load-more" v-if="loading">
                                    <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>
                                    <span>{{ $t('user.friend.loading') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <detail-image v-if="showImage"
            :showImage.sync="showImage"
            :targetNumber.sync="targetNumber"
            :listPhoto="listPhoto">
        </detail-image>
    </div>
</template>

<script>
    import noty from '../../helpers/noty'
    import { mapState, mapActions } from 'vuex'
    import { del, get, post } from '../../helpers/api'
    import ImageModal from '../libs/SelectImageModal.vue'
    import Noty from 'noty'
    import DetailImage from './DetailImage.vue'
    import { EventBus } from '../../EventBus.js'

    export default {
        data: () => ({
            showImage: false,
            targetNumber: 0,
            listPhoto: [],
            page: 1,
            totalPage: 1,
            loading: false
        }),
        computed: {
            ...mapState('user',[
                'currentPageUser',
                'photosUser',
            ]),
            ...mapState('auth',[
                'user',
            ]),
            userId: function () {
                return this.pageId
            },
        },
        created() {
            this.getPhotosUser(this.userId)
            EventBus.$on('photo', () => {
                this.getPhotosUser(this.userId)
                this.page = 1
            })
            this.listPhoto = this.photosUser
        },
        mounted() {
            $(window).scroll(() => {
                if ($(document).height() - $(window).height() < $(window).scrollTop() + 1) {
                    this.loadMoreFriend()
                }
            })
        },
        beforeDestroy() {
            $(window).off()
        },
        methods: {
            showImageDetail(index) {
                this.targetNumber = index
                this.showImage = true
            },
            getPhotosUser() {
                this.loading = true
                get(`user/${this.pageId}/get-photos-user`)
                    .then(res => {
                        this.totalPage = res.data.photos.last_page
                        this.listPhoto = res.data.photos.data
                        this.loading = false
                    })
                    .catch(err => {
                        noty({
                            text: this.$i18n.t('messages.connection_error'),
                            container: false,
                            force: true
                        })
                    })
            },
            loadMoreFriend() {
                if (this.page < this.totalPage) {
                    this.loading = true
                    get(`user/${this.pageId}/get-photos-user?page=${++this.page}`)
                        .then(res => {
                            this.listPhoto = this.listPhoto.concat(res.data.photos.data)
                            this.loading = false
                        })
                        .catch(err => {
                            this.loading = false
                            noty({
                                text: this.$i18n.t('messages.connection_error'),
                                container: false,
                                force: true
                            })
                        })
                }
            },
            deletePhoto(id) {
                del(`delete-photo/${id}`)
                    .then(res => {
                        this.listPhoto = $.grep(this.listPhoto, function (item, index) {
                            return item.id !== id
                        })
                        noty({
                            text: this.$i18n.t('messages.delete_success'),
                            force: false,
                            container: false,
                            type: 'success'
                        })
                    })
                    .catch(err => {
                        noty({
                            text: this.$i18n.t('messages.delete_fail'),
                            type: 'error',
                            force: false,
                            container: false
                        })
                    })
            }
        },
        components: {
            DetailImage
        }
    }
</script>

<style lang="scss" scoped>
    .spinner-load-more {
        text-align: center;
        margin: 20px;
    }
    .photo-item {
        .more {
            z-index: 500;
            .more-dropdown {
                a {
                    padding: 0;
                }
            }
        }
    }
    .info-empty {
        text-align: center;
        span {
            font-size: 20px;
            font-weight: bold;
            i {
                margin-right: 5px;
                font-size: 25px;
            }
        }
    }
    .box-tag-image {
        padding: 10px 0 10px 10px;
        .photo-item {
            padding: 0 12px 3px 0;
            .tag-image {
                height: 206px;
                background-position: 50% 25%;
                background-repeat: no-repeat;
                background-size: cover;
                .overlay {
                    bottom: 3px;
                    right: 12px;
                    border-radius: 0px;
                }
            }
        }
    }
</style>
