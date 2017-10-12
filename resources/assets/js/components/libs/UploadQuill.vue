<template lang="html">
    <transition name="modal">
        <div class="v-modal-mask" @click="onClose" v-show="uploadVisible">
            <div class="v-modal-container" @click.stop>
                <div class="v-modal-header">
                    <button type="button" class="close" aria-label="Close" @click.stop="onClose">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h5>{{ $t('form.title.insert_images') }}</h5>
                </div>
                <transition name="fade">
                    <div class="alert alert-danger" v-if="uploadedErrors.length">
                        <ul v-for="error in uploadedErrors">
                            <li>{{ error }}</li>
                        </ul>
                    </div>
                </transition>
                <div class="v-modal-body">
                    <dropzone
                        ref="myVueDropzone"
                        id="dropzone"
                        url="/api/file/upload-image-for-editor"
                        @vdropzone-success="showSuccess"
                        @vdropzone-error="showError"
                        :thumbnailWidth="100"
                        :thumbnailHeight="100"
                        :dropzone-options="dropzoneOptions"
                        :use-custom-dropzone-options="true">
                    </dropzone>
                </div>

                <div class="image-thumbnails row">
                    <div class="col-md-2 col-xs-3 mb-3" v-for="image in thumbnails">
                        <a href="#" @click.prevent="chooseImage(image.image_default)" class="thumbnail">
                            <img :src="image.image_thumbnail" class="w-100">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import Dropzone from 'vue2-dropzone'
import { get } from '../../helpers/api'

export default {
    props: ['uploadVisible', 'imageInsert'],
    data: () => ({
        dropzoneOptions: {
            autoProcessQueue: true,
            uploadMultiple: true,
            parallelUploads: 1,
            maxNumberOfFiles: 1000,
            maxFileSize: 1,
            acceptedFileTypes: 'image/*',
            showRemoveLink: false,
            headers: {
                Authorization: `Bearer ${localStorage.getItem('access_token')}`
            }
        },
        thumbnails: [],
        uploadedErrors: []
    }),
    computed: {
        dropzone() {
            return this.$refs.myVueDropzone.dropzone
        }
    },
    created() {
        get('file/quill-uploaded-images')
            .then((res) => {
                this.thumbnails = res.data.images
            })
    },
    mounted() {
        document.addEventListener("keydown", (e) => {
            if (this.uploadVisible && e.keyCode == 27) {
                this.onClose()
            }
        });

        this.dropzone.on('complete', (file) => {
            setTimeout(() => {
                this.dropzone.removeFile(file)
            }, 1000)
        })
    },
    methods: {
        onClose() {
            this.$emit('update:uploadVisible', false)
        },
        showSuccess(file, response) {
            this.thumbnails = [...response.media, ...this.thumbnails]
        },
        showError(file, error) {
            this.uploadedErrors.push(error)
            setTimeout(() => {
                this.uploadedErrors = []
            }, 6000)
        },
        chooseImage(image) {
            this.$emit('insertImage', image)
        }
    },
    components: {
        Dropzone
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
            margin: 30px auto;
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

            .alert-danger {
                ul {
                    margin-bottom: 0;
                    list-style-type: disc;
                    padding-left: 40px;
                }
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

    .image-thumbnails {
        max-height: calc(85vh - 250px);
        overflow-y: auto;
        img {
            height: 51px;
        }
    }

    #dropzone {
        border: 2px dashed #e5e5e5;
    }

</style>
