import Vue from 'vue'
import store from './store'
import VueI18n from 'vue-i18n'
import messages from './locale'
import routes from './router'
import makeRouter from './router/middleware'
import VeeValidate, { Validator } from 'vee-validate'
import rules from './validation'
import { config, dictionary } from './validation/config'
// import editor quill
import VueQuillEditor from 'vue-quill-editor'
import { ImageImport } from './helpers/quill-editor/ImageImport'
import { ImageResize } from './helpers/quill-editor/ImageResize'

Quill.register('modules/imageImport', ImageImport)
Quill.register('modules/imageResize', ImageResize)

import * as VueGoogleMaps from 'vue2-google-maps'
import VueTimeago from 'vue-timeago'

Vue.use(VueTimeago, {
    name: 'timeago', // component name, `timeago` by default
    locale: 'en-US',
    locales: {
        // you will need json-loader in webpack 1
        'en-US': require('vue-timeago/locales/en-US.json')
    }
});
const router = makeRouter(routes)

Vue.use(VueQuillEditor)
Vue.use(VueI18n)

// Register rules vee-validation
Vue.use(VeeValidate, config)
for (let rule in rules) {
    Validator.extend(rule, rules[rule])
}

Validator.updateDictionary(dictionary);

const i18n = new VueI18n({
    locale: window.Laravel.locale,
    fallbackLocale: window.Laravel.fallbackLocale,
    messages
})

const app = new Vue({
    el: '#app',
    store,
    router,
    i18n
})
