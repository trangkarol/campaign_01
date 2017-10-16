<template>
    <div v-if="show">
        <div class="background-modal">
        </div>
        <div class="modal fade show wrap-create-action" id="create-photo-album" style="display: block;">
            <div class="modal-dialog ui-block window-popup create-photo-album modal-create-action">
                <div class="ui-block-title">
                    <h6 class="title">{{ $t('actions.create_your_action') }}</h6>
                </div>
                <div class="ui-block-content">
                    <form class="form-group label-floating">
                        <label class="control-label">{{ $t('actions.title_action') }}</label>
                        <input
                            name="title"
                            class="form-control"
                            placeholder=""
                            type="text"
                            v-model="newAction.caption"
                            v-validate="'required|max:255|min:4'"
                            :class="{ 'has-error': errors.has('title')}">
                        <span v-show="errors.has('title')" class="material-input text-danger">
                            {{ errors.first('title') }}
                        </span>
                        <quill-editor
                            data-vv-name="description"
                            id="description"
                            :options="editorOption"
                            v-model="newAction.description">
                        </quill-editor>
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
                    <div class="ui-block-title">
                        <a href="javascript:void(0)" class="btn btn-secondary btn-lg btn--half-width" @click="onClose">
                            {{ $t('actions.cancel') }}
                        </a>
                        <a href="javascript:void(0)" class="btn btn-primary btn-lg btn--half-width" @click="createAction">
                            {{ $t('actions.create_action') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import Dropzone from 'vue2-dropzone'
    import { config } from '../../config'
    import { del, post } from '../../helpers/api'
    import noty from '../../helpers/noty'
    import { mapActions, mapState } from 'vuex'
    export default {
        props: [
            'show',
        ],

        data() {
            return {
                maxFile: config.maxFileUpload,
                maxSizeFile: config.maxSizeFile,
                accessToken: `Bearer ${localStorage.getItem('access_token')}`,
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
                newAction : {
                    caption: '',
                    description: '',
                    files: [],
                    event_id: ''
                },
                accessToken: `Bearer ${localStorage.getItem('access_token')}`,
                pageType: 'event'
            }
        },

        computed: {
            ...mapState('event', ['event']),
        },
        created() {
            this.newAction.event_id = this.pageId
        },
        methods: {
            showSuccess(file, response) {
                this.newAction.files.push(response)
            },

            deleteFile(file, error, xhr) {
                this.hasError = this.$refs.myVueDropzone.getRejectedFiles().length
            },

            createAction() {
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
                    post('action/create', this.newAction)
                        .then(res => {
                            this.$Progress.finish()
                            noty({
                                text: this.$i18n.t('messages.create_success'),
                                force: false,
                                container: false,
                                type: 'success'
                            })
                            this.$socket.emit('created_action', {
                                newAction: res.data.action,
                                room: `event${this.pageId}`
                            })
                            this.onClose()
                        })
                        .catch(err => {
                            this.$Progress.finish()
                            noty({
                                text: this.$i18n.t('messages.create_fail'),
                                type: 'error',
                                force: false,
                                container: false
                            })
                        })
                }
            },

            onClose() {
                this.$emit('update:show', false)
                this.hasError = false
                this.newAction.caption = ''
                this.newAction.description = ''
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

<style lang="scss" scoped>
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
            margin-top: 80px !important;
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
