import * as types from './mutation-types'
import { post, get, patch } from '../../../helpers/api'

export const get_event = ({ commit }, params) => {
    commit('SET_LOADING', false, { root: true })

    return new Promise((resolve, reject) => {
        get(`event/show/${params.eventId}?campaignId=${params.campaignId}`)
            .then(res => {
                commit(types.SET_EVENT, res.data)
                commit('SET_LOADING', true, { root: true })
                resolve(res.data.http_status.status)
            })
            .catch(err => {
                commit('SET_LOADING', true, { root: true })
                reject(err)
            })
    })
};

export const load_action = ({ commit }, para) => {
    if (para.actions.to < para.actions.total) {
        commit(types.SET_LOAD_PAGINATE, true)

        if (!para.flag_search) {
            get(`action/list/${para.event_id}?page=${para.actions.current_page + 1}`)
                .then(res => {
                    commit(types.LOAD_ACTION, res.data)
                    commit(types.SET_LOAD_PAGINATE, false)
                })
                .catch(err => {
                    commit(types.SET_LOAD_PAGINATE, false)
                })
        } else {
            get(`action/search/${para.event_id}?key=${para.key}&page=${para.actions.current_page + 1}`)
                .then(res => {
                    commit(types.LOAD_ACTION, res.data)
                    commit(types.SET_LOAD_PAGINATE, false)
                })
                .catch(err => {
                    commit(types.SET_LOAD_PAGINATE, false)
                })
        }
    }
};

export const search_action = ({ commit }, para) => {
    commit(types.SET_LOAD_SEARCH, true)
    commit(types.SET_FLAG_SEARCH, true)
    commit(types.SET_KEY_SEARCH, para.key_search)
    get(`action/search/${para.event_id}?key=${para.key_search}`)
        .then(res => {
            commit(types.SET_LOAD_SEARCH, false)
            commit(types.SET_ACTION, res.data.actions)
        }).catch(err => {
            commit(types.SET_LOAD_SEARCH, false)
        })
};

export const like_event = ({ commit }, event_id) => {
    post(`event/like/${event_id}`)
};

export const setDetailEvent = ({ commit }, data) => {
    commit(types.SET_DETAIL_EVENT, data)
};

export const update_donate = ({ commit }, payload) => {
    post('donation/create-many', payload)
        .then(res => {
            commit(types.UPDATE_DONATION, res.data.donations)
        })
}

export const setDonationList = ({ commit }, data) => {
    commit(types.UPDATE_DONATION, data)
}

export const change_donate_status = ({ commit }, data) => {
    patch(`donation/update-status/${data.id}`, { status: data.status })
        .then(res => {
            commit(types.CHANGE_DONATION_STATUS, res.data.donation)
        })
}

export const removeAction = ({ commit }, actionId) => {
    commit(types.REMOVE_ACTION, actionId)
}

export const appendOneAction = ({ commit, rootState }, data) => {
    data.rootStateLike = rootState.like
    commit(types.APPEND_ONE_ACTION, data)
}

export const updateDataAction = ({ commit }, data) => {
    commit(types.UPDATE_ACTION, data)
}


export default {
    get_event,
    load_action,
    search_action,
    like_event,
    setDetailEvent,
    update_donate,
    change_donate_status,
    removeAction,
    appendOneAction,
    updateDataAction,
    setDonationList
}
