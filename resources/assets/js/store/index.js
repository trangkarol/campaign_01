import Vue from 'vue'
import Vuex from 'vuex'
import campaign from './modules/campaign'
import auth from './modules/auth'
import comment from './modules/comment'
import event from './modules/event'
import like from './modules/like'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production';

export default new Vuex.Store({
    //Assign the modules to the store
    modules: {
        campaign,
        auth,
        comment,
        event,
    },
    // #root state
    state: {
        loading: false,
    },
    // #root mutations
    mutations: {
        SET_LOADING(state, loading) {
            state.loading = loading
        },
    },
    // If strict mode should be enabled
    strict: debug,
});
