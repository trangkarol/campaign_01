<template lang="html">
    <transition name="modal">
        <div class="v-modal-mask" @click="onClose" v-show="show">
            <div class="v-modal-container" @click.stop>
                <div class="v-modal-header">
                    <button type="button" class="close" aria-label="Close" @click.stop="onClose">
                        <span aria-hidden="true">×</span>
                    </button>
                    <slot name="header"></slot>
                </div>

                <div class="v-modal-body">
                    <slot name="main"></slot>
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
    props: ['show'],
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
        }
    }
}
</script>

<style lang="scss">
</style>
