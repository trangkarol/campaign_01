import * as types from './mutation-types';
import { post, get } from '../../../helpers/api'
import noty from '../../../helpers/noty'

export const setLike = ({ commit }, data) => {
    commit(types.SET_LIKE, data)
};

export const likeActivity = ({ commit }, data) => {
    if (data.deleteDate == null) {
        return new Promise((resolve, reject) => {
            post(`like/${data.modelId}/${data.model}`)
                .then(res => {
                        commit(types.LIKE_ACTIVITY, {
                            like: res.data.like,
                            modelId: data.modelId,
                            flag: data.flag,
                            user: data.user,
                            numberOfLikes: res.data.numberOfLikes,
                            deleteDate: data.deleteDate
                        })

                        resolve(res.data)
                })
                .catch(err => {
                    reject(err)
                })
        })
    } else {
        noty({ text: data.messages, force: true, container: false })
    }
};

export const getListMemberLiked = ({ commit }, data) => {
    if ((parseInt(data.pageCurrent) + 1) <= data.lastPage) {
        return new Promise((resolve, reject) => {
            get(`list-members/${data.modelId}/${data.model}?page=${(parseInt(data.pageCurrent) + 1)}`)
                .then(res => {
                    commit(types.LIST_MEMBERS, {
                        member: res.data.list_member,
                        modelId: data.modelId,
                        flag: data.model,
                    })

                    resolve(res.data.http_status.status)
                })
                .catch(err => {
                    reject(err)
                })
        })
    }
};

export const appendLike = ({ commit, rootState }, data) => {
    commit(types.APPEND_LIKE, data)

};

export default {
    setLike,
    likeActivity,
    getListMemberLiked,
    appendLike
};
