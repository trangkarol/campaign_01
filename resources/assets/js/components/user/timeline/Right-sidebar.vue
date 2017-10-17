<template>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <div class="right-home-box">
            <div class="ui-block">
                <div class="ui-block-title">
                    <router-link class="list-photo title"
                        :to="{ name: 'user.photo', params: { id: currentPageUser.id }}">
                        {{ $t('user.sidebar.list_photo') }}
                    </router-link>
                </div>
                <div class="ui-block-content">
                    <span class="noti" v-if="!listPhoto.length">
                        <i class="fa fa-picture-o" aria-hidden="true"></i>
                        {{ $t('user.sidebar.done_have_photo') }}
                    </span>
                    <ul v-else class="widget w-last-photo js-zoom-gallery">
                        <li v-for="(image, index) in listPhoto">
                            <div class="image-user"
                                @click="showImageDetail(index)"
                                :style="{ 'background-image': `url(${image.image_medium})` }">
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="ui-block">
                <div class="ui-block-title">
                    <h6 class="title">
                        {{ $t('user.sidebar.friends') }}
                        ({{ currentPageUser.friends_count }})
                    </h6>
                </div>
                <div class="ui-block-content">
                    <span class="noti" v-if="!listFriend.length">
                        <i class="fa fa-user-o" aria-hidden="true"></i>
                        {{ $t('user.sidebar.done_have_friend') }}
                    </span>
                    <ul v-else class="widget w-faved-page js-zoom-gallery">
                        <li v-for="friend in listFriend" v-tooltip:top="friend.name">
                            <router-link :to="{ name: 'user.timeline', params: { slug: friend.slug } }">
                                <img :src="friend.image_medium" alt="author">
                            </router-link>
                        </li>
                        <li class="all-users" v-if="currentPageUser.friends_count > paginate">
                            <a href="javascipt:void(0)" @click="showMoreFriends">
                                +{{ currentPageUser.friends_count - paginate }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal fade show" id="create-friend-group-add-friends" v-show="showFriends">
                <div class="modal-dialog ui-block window-popup create-friend-group create-friend-group-add-friends">
                    <a href="javascript:void(0)" @click="closePopup" class="close icon-close" data-dismiss="modal">
                        <svg class="olymp-close-icon">
                            <use xlink:href="/frontend/icons/icons.svg#olymp-close-icon"></use>
                        </svg>
                    </a>
                    <div class="ui-block-title">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        <h6 class="title">
                            {{ $t('user.sidebar.friends') }}
                            ({{ currentPageUser.friends_count }})
                        </h6>
                    </div>
                    <div class="ui-block-content">
                        <form class="form-group label-floating is-select is-empty">
                            <div class="btn-group bootstrap-select show-tick form-control style-2">
                                <div class="dropdown-menu open" role="combobox">
                                    <div class="">
                                        <input type="text"
                                            v-model="keyword"
                                            @input="searchFriends"
                                            :placeholder="$t('user.sidebar.search_friends')"
                                            class="form-search form-control"
                                        >
                                    </div>
                                    <ul class="dropdown-menu inner" role="listbox" aria-expanded="false">
                                        <li v-for="friend in friends">
                                            <a class="img-friend dropdown-item">
                                                <div class="inline-items">
                                                    <div class="author-thumb">
                                                        <img :src="friend.image_medium" alt="author">
                                                    </div>
                                                    <div class="h6 author-title">{{ friend.name }}</div>
                                                </div>
                                                <span class="glyphicon glyphicon-ok check-mark"></span>
                                            </a>
                                        </li>
                                        <li v-if="!keyword && friends.length < currentPageUser.friends_count">
                                            <a class="tag-load-more img-friend dropdown-item">
                                                <div class="div-load inline-items">
                                                     <a @click="loadMoreFriend">{{ $t('user.sidebar.load_more') }}</a>
                                                </div>
                                                <span class="glyphicon glyphicon-ok check-mark"></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <span class="material-input"></span>
                            <span class="material-input"></span>
                        </form>
                    </div>
                </div>
            </div>
            <detail-image v-if="showImage"
                :showImage.sync="showImage"
                :targetNumber.sync="targetNumber"
                :listPhoto="listPhoto">
            </detail-image>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions, mapGetters } from 'vuex'
    import { del, get } from '../../../helpers/api'
    import DetailImage from '../DetailImage.vue'
    import { EventBus } from '../../../EventBus.js';

    export default {
        data: () => ({
            paginate : window.Laravel.pagination.friend,
            showFriends: false,
            page: 2,
            friends: [],
            keyword: '',
            showImage: false,
            targetNumber: 0
        }),
        created() {
            this.getListPhotoAndFriend(this.pageId),
            EventBus.$on('photo', data => {
                this.getListPhotoAndFriend(this.pageId)
            });
        },
        components: {
            DetailImage
        },
        computed: {
            ...mapState('user', [
                'currentPageUser',
                'listPhoto',
                'listFriend',
            ]),
            ...mapState('auth', {
                user: state => state.user,
            })
        },
        watch: {
            $route () {
                this.getListPhotoAndFriend(this.pageId)
            }
        },
        methods: {
            ...mapActions('user', [
                'getListPhotoAndFriend',
            ]),
            showMoreFriends() {
                this.showFriends = true
                get(`user/${this.currentPageUser.id}/friends/${this.page}`)
                    .then(res => {
                        this.friends = this.listFriend.concat(res.data.data)
                    })
            },
            loadMoreFriend() {
                get(`user/${this.currentPageUser.id}/friends/${++this.page}`)
                    .then(res => {
                        this.friends = this.friends.concat(res.data.data)
                    })
            },
            closePopup() {
                this.showFriends = false
                this.friends = this.listFriend
                this.page = 2
            },
            showImageDetail(index) {
                this.targetNumber = index
                this.showImage = true
            },
            searchFriends: _.debounce(function () {
                if (!this.keyword.trim()) {
                    this.friends = this.listFriend
                    this.page = 1
                } else {
                    get(`user/${this.currentPageUser.id}/search-friends/${this.keyword}`)
                        .then(res => {
                            this.friends = res.data.data
                        })
                }
            }, 500)
        }
    }
</script>

<style lang="scss" scoped>
    .list-photo {
        font-weight: 700;
        line-height: 1.3;
        color: #515365;
        font-size: 0.875rem;
    }
    .img-friend {
        .author-thumb {
            width: 40px;
            height: 40px;
        }
        .author-title {
            vertical-align: initial;
        }
        .div-load {
            border-radius: 3px;
            background: #b2b2b2;
            text-align: center;
            > a {
                color: white;
                font-size: 14px;
                padding: 8px;
                color: white !important;
                &:hover {
                    text-decoration: underline;
                }
            }
        }
    }
    .tag-load-more {
        padding: 0px;
    }
    .fa-users {
        padding: 0px;
        font-size: 19px;
        margin: 0px;
        color: #ff5e3a;
        margin-right: -14px;
    }
    .form-search {
        padding: 10px 15px;
    }
    .noti {
        font-size: 14px;
        .fa {
            margin-right: 5px;
        }
    }
    .image-user {
        width: 100%;
        height: 78px;
        border-radius: 3px;
        background-position: 50% 25%;
        background-size: cover;
        background-repeat: no-repeat;
    }
    .w-faved-page {
        li {
            margin: 0 7px 7px 0;
        }
        img {
            width: 34px;
            height: 34px;
        }
    }
    .modal {
        display: block;
        background: rgba(49, 49, 61, 0.54);
        ul {
            height: 440px;
        }
        img {
            width: 34px;
            height: 34px;
        }
    }
</style>
