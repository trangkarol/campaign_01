<template>
    <transition name="fade">
        <div>
            <div class="background-modal"></div>
            <div class="modal show wrap-create-action" id="create-photo-album" style="display: block;">
                <div class="modal-dialog ui-block window-popup create-photo-album modal-create-action">
                    <a href="javascript:void(0)" @click="onClose" class="close icon-close">
                        <svg class="olymp-close-icon">
                            <use xlink:href="/frontend/icons/icons.svg#olymp-close-icon"></use>
                        </svg>
                    </a>
                    <div class="ui-block-title">
                        <h6 class="title">{{ $t('actions.update_your_action') }}</h6>
                    </div>
                    <div class="ui-block-content">
                        <form class="form-group label-floating">
                            <label class="control-label">{{ $t('actions.title_action') }}</label>
                            <input
                                name="title"
                                class="form-control"
                                placeholder=""
                                type="text"
                                v-model="data.caption"
                                v-validate="'required|max:255|min:4'"
                                :class="{ 'has-error': errors.has('title')}">
                            <span v-show="errors.has('title')" class="material-input text-danger">
                                {{ errors.first('title') }}
                            </span>
                            <quill-editor
                                data-vv-name="description"
                                id="description"
                                :options="editorOption"
                                v-model="data.description">
                            </quill-editor>
                            <div class="list-image">
                                <div v-for="(image, index) in dataAction.media">
                                    <img :src="image.image_medium">
                                    <a href="javascript:void(0)" @click="appendIdsMedia($event, image.id)" class="remove-img close icon-close">
                                        <svg class="olymp-close-icon">
                                            <use xlink:href="/frontend/icons/icons.svg#olymp-close-icon"></use>
                                        </svg>
                                    </a>
                                    <i class="recovery-img fa fa-reply-all" aria-hidden="true" @click="recoverIdsMedia($event, image.id)"></i>
                                </div>
                            </div>
                        </form>
                        <div :class="{ 'upload-file': true, 'has-error': hasError }">
                            <dropzone
                                id="myVueDropzoneId"
                                ref="myVueDropzone"
                                url="/api/file/upload"
                                acceptedFileTypes='image/*'
                                :autoProcessQueue="false"
                                :maxNumberOfFiles="maxFile"
                                :maxFileSizeInMB='maxSizeFile'
                                :headers = "{
                                    Authorization: accessToken
                                }"
                                @vdropzone-success="showSuccess"
                                @vdropzone-removed-file="deleteFile"
                                @vdropzone-queue-complete="queueComplete"
                                @vdropzone-files-added="fileAdded">
                            </dropzone>
                        </div>
                        <a href="javascript:void(0)" class="btn btn-secondary btn-lg btn--half-width" @click="onClose">
                            {{ $t('actions.cancel') }}
                        </a>
                        <a href="javascript:void(0)" class="btn btn-primary btn-lg btn--half-width" @click="updateAction">
                            {{ $t('actions.create_action') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
    import Dropzone from 'vue2-dropzone'
    import { config } from '../../config'
    import { del, patch } from '../../helpers/api'
    import noty from '../../helpers/noty'
    import { mapActions } from 'vuex'
    export default {
        props: [
            'showUpdate',
            'dataAction',
        ],

        data() {
            return {
                maxFile: config.maxFileUpload,
                maxSizeFile: config.maxSizeFile,
                editorOption: {
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{ 'header': 1 }, { 'header': 2 }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'indent': '-1'}, { 'indent': '+1' }],
                            [{ 'direction': 'rtl' }],
                            [{ 'size': ['small', false, 'large', 'huge'] }],
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'font': [] }],
                            [{ 'align': [] }],
                            ['clean']
                        ]
                    }
                 },
                hasError: false,
                showImage: true,
                accessToken: `Bearer ${localStorage.getItem('access_token')}`,
                data : {
                    delete: [],
                    caption: '',
                    description: '',
                    files: [],
                    event_id: this.pageId,
                    upload: []
                }
            }
        },

        created() {
            this.data.caption = this.dataAction.caption
            this.data.description = this.dataAction.description
        },

        methods: {
            ...mapActions('event', [
                'get_event'
            ]),

            showSuccess(file, response) {
                this.data.upload.push(response)
            },

            deleteFile(file, error, xhr) {
                this.hasError = this.$refs.myVueDropzone.getRejectedFiles().length
            },

            updateAction() {
                this.hasError = this.$refs.myVueDropzone.getRejectedFiles().length
                this.$validator.validateAll().then((result) => {
                    if (!this.hasError) {
                        this.$refs.myVueDropzone.processQueue()

                        if (!this.$refs.myVueDropzone.getUploadingFiles().length) {
                            this.queueComplete()
                        }
                    }
                })
            },

            queueComplete(file, xhr, formData) {
                this.hasError = this.$refs.myVueDropzone.getRejectedFiles().length

                if (!this.hasError) {
                    this.$Progress.start()
                    patch(`action/update/${this.dataAction.id}`, this.data)
                        .then(res => {
                            this.$Progress.finish()
                            noty({
                                text: this.$i18n.t('messages.update_success'),
                                force: false,
                                container: false,
                                type: 'success'
                            })
                            this.get_event(this.pageId)
                            this.onClose()
                        })
                        .catch(err => {
                            this.$Progress.fail()
                            noty({
                                text: this.$i18n.t('messages.create_fail'),
                                type: 'error',
                                force: false,
                                container: false
                            })
                        })
                }
            },

            appendIdsMedia(event, id) {
                this.data.delete.push(id)
                $(event.currentTarget).css('display', 'none')
                $(event.currentTarget).siblings('.recovery-img').css('display', 'block')
            },

            recoverIdsMedia(event, id) {
                this.data.delete = this.data.delete.filter(function(item) {
                    return item !== id
                })
                $(event.currentTarget).css('display', 'none')
                $(event.currentTarget).siblings('.remove-img').css('display', 'block')
            },

            onClose() {
                this.$emit('update:showUpdate', false)
                this.data.caption = ''
                this.data.description = ''
            },

            fileAdded() {
                this.hasError = this.$refs.myVueDropzone.getRejectedFiles().length
            }
        },

        components: {
            Dropzone
        }
    }
</script>

<style lang="scss">
    .list-image {
        margin-top: 20px;
        > div {
            display: inline-block;
            position: relative;
            >img {
                border: 5px solid #fff;
                margin: 2px;
                max-height: 120px;
                max-width: 200px;
                box-shadow: 0 1px 1px 0 rgba(0,0,0,.14), 0 2px 1px -1px rgba(0,0,0,.12), 0 1px 3px 0 rgba(0,0,0,.2);
            }
            .remove-img {
                position: absolute;
                right: 12px;
                top: 12px;
            }
            &:hover {
                >img {
                    opacity: 0.8;
                }
            }

            .recovery-img {
                position: absolute;
                display: none;
                right: 12px;
                top: 12px;
                font-size: 45px;
                color: #5c9fe9;
                &:hover {
                    color: #0085ff;
                }
            }
        }
    }

    .background-modal {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1000;
        background: black;
        opacity: 0.5
    }
    .wrap-create-action {
        overflow-y: scroll;
        .modal-create-action {
            width: 960px !important;
            margin-top: 65px !important;
            .quill-editor {
                min-height: 200px;
                height: auto;
                margin: 10px 0;
                .ql-container {
                    min-height: 132px;
                    height: auto;
                    .ql-editor {
                        height: auto;
                        min-height: 132px;
                    }
                }
            }
            .upload-file {
                min-height: 300px;
                form {
                    min-height: 300px;
                }
            }
            .has-error {
                border: 1px solid red;
                border-radius: 2px;
            }
        }
        &::-webkit-scrollbar {
            display: none;
        }
        .dz-error-message {
            top: 5px !important;
            left: 59px !important;
        }
    }
    .vue-dropzone {
        .dz-preview {
            .dz-error-mark {
                i {
                    color: red !important;
                }
            }
        }
    }
</style>
