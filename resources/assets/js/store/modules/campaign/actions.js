/* ============
 * Actions for the account module
 * ============
 *
 * The actions that are available on the
 * account module.
 */

import * as types from './mutation-types';
import { post, get, patch } from '../../../helpers/api'

export const campaignDetail = ({ commit }, campaignId) => {
    return new Promise((resolve, reject) => {
        get(`campaign/${campaignId}`)
            .then(res => {
                commit(types.CAMPAIGN_DETAIL, res.data)
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const getlistPhotos = ({ commit }, campaignId) => {
    get(`campaign/list-photos/${campaignId}`)
        .then(res => {
            commit(types.LIST_PHOTOS, res.data.list_photos)
        })
};

export const fetchData = ({ commit }, data) => {
    commit(types.LOADING, true)

    if (data.pageNumberEvent >= (parseInt(data.pageCurrent) + 1)) {
        get(`campaign/${data.campaignId}/timeline/event?page=${(parseInt(data.pageCurrent) + 1)}`)
            .then(res => {
                commit(types.FETCH_DATA, res.data.events)
                commit(types.LOADING, false)
            })
    } else {
        commit(types.LOADING, false)
    }
};

export const attendCampaign = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        post(`campaign/attend-campaign/${data.campaignId}/${data.flag}`)
            .then(res => {
                if (res.data.http_status.status) {
                    if (data.flag == 'join') {
                        commit(types.JOIN_CAMPAIGN, res.data.attend_campaign)
                    } else {
                        commit(types.LEAVE_CAMPAIGN, res.data.attend_campaign)
                    }

                    resolve(res.data.http_status.status)
                }
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const loadMorePhotos = ({ commit }, data) => {
    if ((parseInt(data.currentPage) + 1) <= data.lastPage) {
        return new Promise((resolve, reject) => {
            get(`campaign/list-photos/${data.campaignId}?page=${(parseInt(data.currentPage) + 1)}`)
                .then(res => {
                    commit(types.LOAD_MORE_LIST_PHOTOS, res.data.list_photos)
                    resolve(res.data.http_status.status)
                })
                .catch(err => {
                    reject(err)
                })
        })
    }
};

export const getListMembers = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        get(`campaign/members/${data.campaignId}/${data.status}`)
            .then(res => {
                resolve(res.data.members)
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const changeStatusMember = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        post(`campaign/change-status-user/${data.campaignId}/${data.userId}/${data.flag}`)
            .then(res => {
                if (data.flag == 'approve') {
                    commit(types.APPROVE_JOIN_CAMPAIGN, res.data.change_status)
                }

                resolve(res.data.http_status.status)
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const loadMoreMembers = ({ commit }, data) => {
    if ((parseInt(data.pageCurrent) + 1) < data.pageNumberEvent) {
        return new Promise((resolve, reject) => {
            get(`campaign/search-members/${data.campaignId}/${data.status}?=${(parseInt(data.pageCurrent) + 1)}`)
                .then(res => {
                    resolve(res.data.members)
                })
                .catch(err => {
                    reject(err)
                })
        })
    }
};

export const searchMember = ({ commit }, data) => {
    if ((parseInt(data.pageCurrent) + 1) <= data.pageNumberEvent) {
        return new Promise((resolve, reject) => {
            get(`campaign/search-members/${data.campaignId}/${data.status}?search=${data.search}&roleId=${data.roleId}&page=${(parseInt(data.pageCurrent) + 1)}`)
                .then(res => {
                    resolve(res.data)
                })
                .catch(err => {
                    reject(err)
                })
        })
    }
};

export const changeMemberRole = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        patch(`campaign/change-role`, data)
            .then(res => {
                commit(types.BLOCKED_CAMPAIGN, data.userId)

                resolve(res.data.http_status.status)
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const openCampaign = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        post(`campaign/open/${data.campaignId}`)
            .then(res => {
                resolve(res.data)
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const searchMemberToInvite = ({ commit }, data) => {
    if ((parseInt(data.pageCurrent) + 1) <= data.pageNumberEvent) {
        return new Promise((resolve, reject) => {
            get(`campaign/search-members-invite/${data.campaignId}?search=${data.search}&page=${(parseInt(data.pageCurrent) + 1)}`)
                .then(res => {
                    resolve(res.data)
                })
                .catch(err => {
                    reject(err)
                })
        })
    }
};

export const setDetailcampaign = ({ commit }, data) => {
    commit(types.SET_DETAIL_CAMPAIGN, data)
};

export const updateEventsCampaign = ({ commit, rootState }, data) => {
    commit(types.UPDATE_EVENTS_CAMPAIGN, { event: data,  rootStateLike: rootState.like })
};

export const inviteUser = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        post(`campaign/invite-user/${data.campaignId}/${data.userId}`)
            .then(res => {
                resolve(res.data)
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const acceptCampaign = ({ commit }, campaignId) => {
    return new Promise((resolve, reject) => {
        post(`campaign/accept/${campaignId}`)
            .then(res => {
                commit(types.ACCEPT_CAMPAIGN, {
                    user: res.data.user,
                    isManager: res.data.is_manager
                })

                resolve(res.data)
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const listMember = ({ commit }, data) => {
    if ((parseInt(data.pageCurrent) + 1) <= data.pageNumberEvent) {
        return new Promise((resolve, reject) => {
            get(`campaign/members/${data.campaignId}/${data.status}?search=${data.search}&roleId=${data.roleId}&page=${(parseInt(data.pageCurrent) + 1)}`)
                .then(res => {
                    resolve(res.data)
                })
                .catch(err => {
                    reject(err)
                })
        })
    }
};

export default {
    campaignDetail,
    fetchData,
    attendCampaign,
    getlistPhotos,
    loadMorePhotos,
    setDetailcampaign,
    getListMembers,
    changeStatusMember,
    loadMoreMembers,
    searchMember,
    changeMemberRole,
    openCampaign,
    searchMemberToInvite,
    updateEventsCampaign,
    inviteUser,
    acceptCampaign,
    listMember
};
