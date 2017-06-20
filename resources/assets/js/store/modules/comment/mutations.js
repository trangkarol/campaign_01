import * as types from './mutation-types'
import {get, post } from 'axios'

export default {
    [types.GET_COMMENT](state, data) {
        state.comments = data.comments
        state.modelId = data.modelId
    },

    [types.CHANGE_COMMENT](state, data) {
        // state.comments = data.comments
        state.comments[data[0].commentable_id] = []

        data.forEach(function(item, index) {
            state.comments[item.commentable_id][index] = item
        })

        console.log('mutation', state.comments)
    },

    [types.COMMENT_DETAIL](state, data) {
        state.comments[data.modelId] = data.comments
        console.log('mutation2', data.comments, state.comments)
    }
};