import * as types from './mutation-types'
import axios from 'axios'

export default {
    [types.SET_CURRENT_PAGE_USER](state, data) {
        state.currentPageUser = data.currentPageUser
        state.listActivity = data.listActivity
        state.checkLiked = data.checkLiked
        state.inforListActivity = data.inforListActivity
    },
    [types.SET_LIST_ACTIVITY](state, data) {
        let old_data = state.listActivity
        state.listActivity = data.listActivity
        state.checkLiked = data.checkLiked
        state.listActivity = $.merge(old_data, state.listActivity)
        state.inforListActivity = data.inforListActivity
    },
    [types.SET_LOADING](state, loading) {
        state.loading = loading
    },
    [types.UPDATE_CURRENT_PAGE_USER](state, data) {
        state.currentPageUser = data
    },
    [types.SET_LIST_PHOTO_AND_FRIEND](state, data) {
        state.listPhoto = data.listPhoto
        state.listFriend = data.listFriend
    }
};
