import * as types from './mutation-types';
import { get, post } from '../../../helpers/api'

export const setLike = ({ commit }, data) => {
    commit(types.SET_LIKE, data)
};

export const likeActivity = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        post(`like/${data.modelId}/${data.model}`)
            .then(res => {
                if (res.data.http_status.status) {
                    commit(types.LIKE_ACTIVITY, {
                        like: res.data.like,
                        modelId: data.modelId,
                        flag: data.flag,
                        user: data.user,
                        numberOfLikes: res.data.numberOfLikes
                    })

                    resolve(res.data.http_status.status)
                }
            })
            .catch(err => {
                reject(err)
            })
    })
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
                })
                .catch(err => {
                    reject(err)
                })
        })
    }
};

export default {
    setLike,
    likeActivity,
    getListMemberLiked
};
