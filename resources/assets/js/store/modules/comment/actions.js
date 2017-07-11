/* ============
 * Actions for the account module
 * ============
 *
 * The actions that are available on the
 * account module.
 */

import * as types from './mutation-types';
import { get, post, patch, del } from '../../../helpers/api'

export const changeComment = ({ commit }, data) => {
    commit(types.CHANGE_COMMENT, data)
};

export const addComment = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        post('comment/create-comment/' + data.modelId + '/' + data.commentParentId + '/' + data.flag, data.comment)
            .then(res => {
                if (res.data.http_status.status) {
                    commit(types.COMMENT_DETAIL, { comments: res.data.comment.data, modelId: data.modelId })

                    resolve(res.data.http_status.status)
                }
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const editComment = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        patch('comment/' + data.commentId, data.comment)
            .then(res => {
                if (res.data.http_status.status) {
                    commit(types.COMMENT_DETAIL, { comments: res.data.comment.data, modelId: data.modelId })
                    resolve(res.data.http_status.status)
                }
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const deleteComment = ({ commit }, data) => {
    del('comment/' + data.commentId, { modelId: data.modelId })
        .then(res => {
            if (res.data.http_status.status) {
                commit(types.COMMENT_DETAIL, { comments: res.data.comment.data, modelId: data.modelId })
            }
        })
};

export default {
    addComment,
    changeComment,
    editComment,
    deleteComment
};
