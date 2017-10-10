<template>
    <div>
        <div class="post-thumb" @click="showImageDetail(0)" v-if="listImage.length < 2">
            <img :src="listImage[0].image_medium" alt="photo">
        </div>

        <div class="post-block-photo js-zoom-gallery" v-else-if="listImage.length < 5">
            <a href="javascript:void(0)"
                class="half-width"
                v-for="(image,index) in listImage"
                v-if="listImage.length == 2 || listImage.length == 4"
                @click="showImageDetail(index)">
                <div :style="{ 'background-image': `url(${image.image_large})` }"></div>
            </a>
            <a href="javascript:void(0)"
                class="col-3-width"
                v-for="(image,index) in listImage"
                v-if="listImage.length == 3"
                @click="showImageDetail(index)">
                <div :style="{ 'background-image': `url(${image.image_large})` }"></div>
            </a>
        </div>

        <div class="post-block-photo js-zoom-gallery" v-else>
            <a href="javascript:void(0)" @click="showImageDetail(0)" class="half-width">
                <div :style="{ 'background-image': `url(${listImage[0].image_large})` }"></div>
            </a>
            <a href="javascript:void(0)" @click="showImageDetail(1)" class="half-width">
                <div :style="{ 'background-image': `url(${listImage[1].image_large})` }"></div>
            </a>
            <a href="javascript:void(0)" @click="showImageDetail(2)" class="col-3-width">
                <div :style="{ 'background-image': `url(${listImage[2].image_large})` }"></div>
            </a>
            <a href="javascript:void(0)" @click="showImageDetail(3)" class="col-3-width">
                <div :style="{ 'background-image': `url(${listImage[3].image_large})` }"></div>
            </a>
            <a href="javascript:void(0)"
                @click="showImageDetail(4)"
                :class="{'more-photos': (listImage.length > 5 ), 'col-3-width': true}">
                <div :style="{ 'background-image': `url(${listImage[4].image_large})` }"></div>
                <span class="h2" v-if="listImage.length > 5">+{{ listImage.length - 5 }}</span>
            </a>
        </div>

        <detail-image v-if="showImage"
            :showImage.sync="showImage"
            :targetNumber.sync="targetNumber"
            :listPhoto="listImage">
        </detail-image>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import ShowText from '../libs/ShowText.vue'
    import DetailImage from '../user/DetailImage.vue'

    export default {
        data: () => ({
            showImage: false,
            targetNumber: 0
        }),
        props: {
            listImage: {},
        },
        methods: {
            showImageDetail(index) {
                this.targetNumber = index
                this.showImage = true
            }
        },
        components: {
            DetailImage
        }
    }
</script>

<style lang="scss" scoped>
    .post-thumb {
        img {
            width: initial;
        }
    }

    .post-block-photo {
        div {
            background-position: 50% 25%;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .half-width {
            > div {
                width: 275px;
                height: 275px;
            }
        }

        .col-3-width {
            > div {
                width: 181px;
                height: 181px;
            }
        }
    }
</style>
