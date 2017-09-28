<template lang="html">
        <div class="div-input-chat form-group label-floating">
            <div ref="chatEditor" class="chat-quill-editor"></div>
            <div class="add-options-message">
                <div class="options-message smile-block">
                    <svg class="olymp-happy-sticker-icon">
                        <use xlink:href="/frontend/icons/icons.svg#olymp-happy-sticker-icon"></use>
                    </svg>
                    <ul class="more-dropdown more-with-triangle triangle-bottom-right">
                        <li v-for="number in 27">
                            <a href="#" @click.prevent>
                                <img :src="`/images/icon-chat${number}.png`" alt="icon" @click="addIcon(number)">
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
</template>

<script>
import quillChat from 'quill'

export default {
    data() {
        return {
            qChat: null,
            content: ''
        }
    },
    props: ['value'],
    mounted() {
        // keybinding: Enter or ...optional extension
        const bindings = {
            handleEnter: this.handleEnter()
        }

        // create instance quilljs editor
        this.qChat = new quillChat(this.$refs.chatEditor, {
            modules: {
                keyboard: { bindings }
            },
            placeholder: this.$i18n.t('chat.send_message')
        })

        // Event Selection Change such as focus, keyup, slection
        this.onSelectionChange()

        // set editor content
        if (this.value) {
          this.qChat.pasteHTML(this.value)
        }
    },
    methods: {
        addIcon(number) {
            const urlImage = `/images/icon-chat${number}.png`
            this.qChat.focus()
            const { index } = this.qChat.getSelection()
            const length = this.qChat.getLength()
            this.qChat.insertEmbed(index, 'image', urlImage)

            // set cursor at the end line
            this.qChat.setSelection(length, length)
        },
        onSelectionChange() {
            this.qChat.on('selection-change', (range) => {
                if (range) {
                    this.$emit('mark-read')
                }
            });
        },
        handleEnter() {
            const self = this

            return {
                key: 13,
                handler: function (range, context) {
                    const text = self.qChat.getText()

                    // update content of editor that was preventing XSS
                    self.qChat.setContents(self.qChat.getContents())

                    // get content's quill editor by html
                    var html = self.$refs.chatEditor.children[0].innerHTML
                    if (html === '<p><br></p>') {
                        html = ''
                    }

                    // Retrieves the length of the editor contents.
                    // When Quill is empty, there is still a blank line represented by ‘\n’, so getLength will return 1.
                    if (self.qChat.getLength() > 1) {
                        self.content = html.replace('<p><br></p>', '')
                        self.$emit('input', self.content)
                        // optional when on text-change
                        self.$emit('text-change', {
                            text: text,
                            quill: self.qChat,
                            html: html
                        })
                    }
                    // Set entire of content of editor quill to new line
                    self.qChat.setText('\n')
                }
            }
        }
    }
}
</script>

<style lang="scss" scoped>

</style>
