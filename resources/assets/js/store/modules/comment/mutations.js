import * as types from './mutation-types'
import { get, post } from 'axios'

export default {
    [types.CHANGE_COMMENT](state, data) {
        state.comments[data.modelId] = []

        data.comments.forEach(function(item, index) {
            state.comments[item.commentable_id][index] = item
        })
    },

    [types.COMMENT_DETAIL](state, data) {
        let comments = state.comments
        comments[data.modelId] = data.comments
        state.comments = []
        state.comments = comments
    }
};
