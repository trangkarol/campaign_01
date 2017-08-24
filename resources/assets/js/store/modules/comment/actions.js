import * as types from './mutation-types';
import { post, patch, del, get } from '../../../helpers/api'

export const changeComment = ({ commit }, comments) => {
    commit(types.CHANGE_COMMENT, comments)
};

export const addComment = ({ commit, rootState }, data) => {
    return new Promise((resolve, reject) => {
        post(`comment/create-comment/${data.modelId}/${data.commentParentId}/${data.flag}`, data.comment)
            .then(res => {
                if (res.data.http_status.status) {

                    if (data.commentParentId == 0) {
                        commit(types.PARENT_COMMENT, {
                            comments: res.data.createComment,
                            modelId: data.modelId,
                            numberComment: res.data.numberComment,
                            flag: data.flag,
                            flagAction: data.flagAction,
                            rootStateLike: rootState
                        })
                    } else {//when comment is parent
                        commit(types.SUB_COMMENT, {
                            comments: res.data.createComment,
                            modelId: data.modelId,
                            numberComment: res.data.numberComment,
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

export const editComment = ({ commit, rootState }, data) => {
    return new Promise((resolve, reject) => {
        post(`comment/update-comment/${data.commentId}/${data.flag}`, data.comment)
            .then(res => {
                if (res.data.http_status.status) {
                    if (data.commentParentId == 0) { //when comment is parent
                        commit(types.PARENT_COMMENT, {
                            comments: res.data.updateComment,
                            modelId: data.modelId,
                            numberComment: res.data.numberComment,
                            flag: data.flag,
                            flagAction: data.flagAction,
                            rootStateLike: rootState
                        })
                    } else {
                        commit(types.SUB_COMMENT, {
                            comments: res.data.updateComment,
                            modelId: data.modelId,
                            numberComment: res.data.numberComment,
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

export const deleteComment = ({ commit, rootState }, data) => {
    return new Promise((resolve, reject) => {
        del(`comment/${data.commentId}`, { modelId: data.modelId, flag: data.flag })
            .then(res => {
                if (res.data.http_status.status) {
                    data.numberComment = res.data.numberComment

                    if (data.commentParentId == 0) { //when comment is parent
                        data.rootStateLike = rootState
                            commit(types.DELETE_PARENT_COMMENT, data)
                    } else {
                        commit(types.DELETE_SUB_COMMENT, data)
                    }
                }

                resolve(res.data.http_status.status)
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const loadMoreParentComment = ({ commit, rootState }, data) => {
    if (data.lastPage >= (parseInt(data.pageCurrent) + 1)) {
        return new Promise((resolve, reject) => {
            get(`comment/${data.modelId}?page=${(parseInt(data.pageCurrent) + 1)}`)
                .then(res => {
                    commit(types.LOAD_MORE_PARENT_COMMENT, {
                        comments: res.data.loadMore,
                        modelId: data.modelId,
                        flag: data.flag,
                        rootStateLike: rootState
                    })

                    resolve(res.data.http_status.status)
                })
                .catch(err => {
                    reject(err)
                })
        })
    }
};

export const loadMoreSubComment = ({ commit, rootState }, data) => {
    if (data.lastPage >= (parseInt(data.pageCurrent) + 1)) {
        return new Promise((resolve, reject) => {
            get(`comment/sub-comment/${data.commentParentId}?page=${(parseInt(data.pageCurrent) + 1)}`)
                .then(res => {
                    commit(types.LOAD_MORE_SUB_COMMENT, {
                        comments: res.data.subComment,
                        modelId: data.modelId,
                        flag: data.flag,
                        commentParentId: data.commentParentId,
                        rootStateLike: rootState
                    })

                    for (let subComment of res.data.subComment.data) {
                        rootState.like.like['comment'][subComment.id] = subComment.likes
                        rootState.like.checkLike['comment'][subComment.id] = subComment.checkLike
                    }

                    resolve(res.data.http_status.status)
                })
                .catch(err => {
                    reject(err)
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
