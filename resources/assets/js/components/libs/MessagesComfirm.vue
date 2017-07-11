<template lang="html">
    <modal></modal>
</template>

<script>
import Modal from './Modal.vue'

export default {
    props: ['flag'],
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
