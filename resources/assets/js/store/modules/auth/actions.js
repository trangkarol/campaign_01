/* ============
 * Actions for the account module
 * ============
 *
 * The actions that are available on the
 * account module.
 */

import * as types from './mutation-types'
import * as routes from '../../../router/router'
import { post, get } from '../../../helpers/api'

export const check = ({ commit }) => {
    commit(types.CHECK);
};

export const login = ({ commit }, data) => {
    commit('SET_LOADING', true, { root: true })

    return new Promise((resolve, reject) => {
        post(routes.handleLogin, data)
            .then(res => {
                commit(types.LOGIN, res.data)
                commit('SET_LOADING', false, { root: true });
                resolve(res.data.http_status.status)
            })
            .catch(err => {
                commit('SET_LOADING', false, { root: true });
                reject(err)
            })
    })
};

export const logout = ({ commit }) => {
    commit('SET_LOADING', false, { root: true });
    window.Laravel.url_after_login = null
    commit(types.LOGOUT);
};

export const setUser = ({ commit }, user) => {
    commit(types.SET_USER, user);
};

export const getListFollow = ({ commit }, list) => {
    return new Promise((resolve, reject) => {
        get(routes.follow)
            .then(res => {
                commit(types.GET_LIST_FOLLOW, res.data.followings);
                commit(types.GET_GROUPS, res.data.groups)
                resolve(res.data.http_status.status)
            })
            .catch(err => {
                commit(types.GET_LIST_FOLLOW, []);
                reject(err)
            })
    })
};

export const updateHeaderPhoto = ({ commit }, image) => {
    post(routes.updateHeaderImage, image)
        .then(res => {
            commit(types.CHANGE_HEADER_PHOTO, image)
        })
};

export const changeAvatar = ({ commit }, image) => {
    post(routes.updateAvatar, image)
        .then(res => {
            commit(types.CHANGE_AVATAR, image)
        })
};

export const uploadImage = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        post(routes.uploadImages + data.path, data.formData)
            .then(res => {
                resolve(res.data.http_status.status)
            })
            .catch(err => {
                reject(err)
            })
    })
};

export default {
    check,
    login,
    logout,
    setUser,
    getListFollow,
    updateHeaderPhoto,
    changeAvatar,
    uploadImage
};
