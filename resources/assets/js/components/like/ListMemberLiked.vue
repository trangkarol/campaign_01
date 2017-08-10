<!-- Window-popup Create Friends Group Add Friends -->
<template lang="html">
    <transition name="modal">
        <div class="v-modal-mask" @click="closeListMember" v-if="flagShowListMember">
            <div class="v-modal-container" @click.stop>
                <div class="v-modal-body">
                    <div class="modal-dialog ui-block window-popup create-friend-group create-friend-group-add-friends">
                        <a href="javascript:void(0)" class="close icon-close" data-dismiss="modal" aria-label="Close"
                            @click="closeListMember()">
                            <svg class="olymp-close-icon"><use xlink:href="/frontend/icons/icons.svg#olymp-close-icon"></use></svg>
                        </a>
                        <div class="ui-block-title">
                            <h6 class="title">{{ $t('messages.members') }}</h6>
                        </div>
                        <div class="ui-block-content">
                            <form class="form-group label-floating is-select">
                                <div class="btn-group bootstrap-select show-tick form-control style-2">
                                    <ul class="dropdown-menu scroll-list-member inner">
                                        <li class="member" v-for="member in listMember[flag][modelId].data">
                                            <router-link :to="{ name: 'user.timeline', params: { id: member.user.id }}">
                                                <span class="text">
                                                    <div class="inline-items">
                                                        <div class="author-thumb">
                                                            <img :src="member.user.image_thumbnail" :alt="member.user.name">
                                                        </div>
                                                        <div class="h6 author-title">{{ member.user.name }}</div>
                                                    </div>
                                                </span>
                                                <span class="glyphicon glyphicon-ok check-mark"></span>
                                            </router-link>
                                        </li>
                                    </ul>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="v-modal-footer text-right">
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
    import Modal from '../libs/Modal.vue'
    import { mapState, mapActions } from 'vuex'

    export default {
        created() {
            this.getListMemberLiked({
                modelId: this.modelId,
                model: this.flag,
                lastPage: 1,
                pageCurrent: 0
            })
        },
        computed: {
            ...mapState('like', [
                'listMember'
            ])
        },
        mounted() {
            $('.scroll-list-member').scroll(() => {
                if ($('.scroll-list-member').scrollTop() + $('.scroll-list-member').innerHeight() >= $('.scroll-list-member')[0].scrollHeight) {
                    this.getListMemberLiked({
                        modelId: this.modelId,
                        model: this.flag,
                        lastPage: this.listMember[this.flag][this.modelId].last_page,
                        pageCurrent: this.listMember[this.flag][this.modelId].current_page
                    })
                }
            })
        },
        methods: {
            closeListMember() {
                this.$emit('update:flagShowListMember', false)
            },
            ...mapActions('like', [
                'getListMemberLiked'
            ]),
        },
        props: [
            'flagShowListMember',
            'modelId',
            'flag',
        ],
        components: {
            Plugin,
            Modal
        }
    }
</script>

<style lang="scss" scoped>
    .v-modal-mask {
        position: fixed;
        z-index: 9998;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        transition: opacity .3s ease;
        .v-modal-container {
            @media screen and (min-width: 992px) {
                width: 500px;
            }
            @media screen and (min-width: 768px) {
               width: 600px;
            }
            margin: 30px auto;
            margin-top: 70px;
            .v-modal-header h3 {
                color: #42b983;
            }

        }
    }
    .modal-enter {
        opacity: 0;
    }
    .modal-leave-active {
        opacity: 0;
    }
    .modal-enter .v-modal-container,
    .modal-leave-active .v-modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }
    .bootstrap-select {
        &.btn-group {
            .scroll-list-member {
                overflow-y: scroll;
            }

            .dropdown-menu {
                &.inner {
                    max-height: 490px !important;
                    .member {
                        height: 34px;
                        margin: 12px 0;
                        .author-thumb {
                            img {
                                height: 34px;
                            }
                        }
                    }
                }
            }
        }
    }
</style>
