<!-- Here is write plugin for sidebar campaign, active, event such us social sharing, etc -->
<template lang="html">
    <div>
        <a href="javascript:void(0)"
            @click="isPopupShare = !isPopupShare"
            class="btn btn-control">
            <svg class="olymp-share-icon">
                <use xlink:href="/frontend/icons/icons.svg#olymp-share-icon"></use>
            </svg>
        </a>
        <slot name="sharing-social" :isPopupShare="isPopupShare"></slot>
    </div>
</template>

<script>
export default {
    data: () => ({
        isPopupShare: false
    }),
}
</script>

<style type="scss" scoped>

</style>