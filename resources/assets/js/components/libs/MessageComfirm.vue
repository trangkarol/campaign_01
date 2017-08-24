<template lang="html">
    <transition name="modal">
        <div class="v-modal-mask" @click="onClose" v-show="show">
            <div class="v-modal-container" @click.stop>
                <div class="v-modal-header">
                    <button type="button" class="close" aria-label="Close" @click.stop="onClose">
                        <span aria-hidden="true">×</span>
                    </button>
                     <h5 class="exclamation-header" slot="header">
                        {{  messages }}
                    </h5>
                </div>

                <div class="v-modal-body" slot="main">
                    <a href="javascript:void(0)"
                        class="btn btn-breez col-lg-3 col-md-6 col-sm-12 col-xs-12"
                        @click="method">
                        {{ $t('form.button.agree') }}
                    </a>
                    <a href="javascript:void(0)"
                        class="btn btn-secondary col-lg-3 col-md-6 col-sm-12 col-xs-12"
                        @click="onClose">
                        {{ $t('form.button.cancel') }}
                    </a>
                </div>

                <div class="v-modal-footer text-right">
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    props: [
        'show',
        'messages',
    ],
    mounted() {
        document.addEventListener("keydown", (e) => {
            if (this.show && e.keyCode == 27) {
                this.onClose()
            }
        });
    },
    methods: {
        onClose() {
            this.$emit('update:show', false)
        },
        method() {
            this.$emit('handelMethod')
        }
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
                width: 900px;
            }
            @media screen and (min-width: 768px) {
               width: 600px;
            }
            width: auto;
            position: absolute;
            top: 35%;
            left: 50%;
            margin-right: -50%;
            transform: translate(-50%, -50%);
            padding: 20px 30px;
            background-color: #fff;
            border-radius: 2px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
            transition: all .3s ease;
            font-family: Helvetica, Arial, sans-serif;

            .v-modal-header h3 {
                margin-top: 0;
                color: #42b983;
            }

            .v-modal-body {
                margin: 20px 0;
            }
        }
    }

    .modal-enter, .modal-leave {
        opacity: 0;
    }

    .modal-enter .v-modal-container,
    .modal-leave .v-modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }
</style>
