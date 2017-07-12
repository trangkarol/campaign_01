/* ============
 * Actions for the account module
 * ============
 *
 * The actions that are available on the
 * account module.
 */

import * as types from './mutation-types';
import { get, post, patch, del } from '../../../helpers/api'

export const changeComment = ({ commit }, comments) => {
    commit(types.CHANGE_COMMENT, comments)
};

export const addComment = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        post('comment/create-comment/' + data.modelId + '/' + data.commentParentId + '/' + data.flag, data.comment)
            .then(res => {
                if (res.data.http_status.status) {

                    if (data.commentParentId == 0) { //when comment is parent
                        commit(types.PARENT_COMMENT, {
                            comments: res.data.createComment,
                            modelId: data.modelId,
                            flag: data.flag,
                            flagAction: data.flagAction
                        })
                    } else {
                        commit(types.SUB_COMMENT, {
                            comments: res.data.createComment,
                            modelId: data.modelId,
                            flag: data.flag,
                            flagAction: data.flagAction
                        })
                    }

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
                    if (data.commentParentId == 0) { //when comment is parent
                        commit(types.PARENT_COMMENT, {
                            comments: res.data.updateComment,
                            modelId: data.modelId,
                            flagAction: data.flagAction
                        })
                    } else {
                        commit(types.SUB_COMMENT, {
                            comments: res.data.updateComment,
                            modelId: data.modelId,
                            flagAction: data.flagAction
                        })
                    }

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
                if (data.commentParentId == 0) { //when comment is parent
                    commit(types.DELETE_PARENT_COMMENT, data)
                } else {
                    commit(types.DELETE_SUB_COMMENT, data)
                }
            }
        })
};

export const loadMoreParentComment = ({ commit }, data) => {
    if (data.lastPage >= (parseInt(data.pageCurrent) + 1)) {
        get('comment/' + data.modelId + '?page=' + (parseInt(data.pageCurrent) + 1))
            .then(res => {
                commit(types.LOAD_MORE_PARENT_COMMENT, { comments: res.data.loadMore.data, modelId: data.modelId })
            })
    }
};

export const loadMoreSubComment = ({ commit }, data) => {
    if (data.lastPage >= (parseInt(data.pageCurrent) + 1)) {
        get('comment/sub-comment/' + data.commentParentId + '?page=' + (parseInt(data.pageCurrent) + 1))
            .then(res => {
                commit(types.LOAD_MORE_SUB_COMMENT, {
                    comments: res.data.subComment,
                    modelId: data.modelId,
                    commentParentId: data.commentParentId
                })
            })
    }
};

export default {
    addComment,
    changeComment,
    editComment,
    deleteComment,
    loadMoreParentComment,
    loadMoreSubComment
};
