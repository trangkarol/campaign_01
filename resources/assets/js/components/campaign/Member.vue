<!-- Window-popup Create Friends Group Add Friends -->
<template>
    <div class="v-modal-mask" @click="closeListMember" v-show="flagShowListMember">
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
                            <plugin></plugin>
                        </form>
                    </div>
                </div>
            </div>

            <div class="v-modal-footer text-right">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</template>

<script>
    import Plugin from './Plugin.vue'
    import Modal from '../libs/Modal.vue'
    import { mapState } from 'vuex'

    export default {
        computed: {
            //
        },
        methods: {
            closeListMember() {
                this.$emit('update:flagShowListMember', false)
            }
        },
        props: ['flagShowListMember'],
        components: {
            Plugin,
            Modal
        }
    }
</script>

<style lang="scss" scoped>
    .v-modal-container {
        margin-top: 7%;
    }
</style>
