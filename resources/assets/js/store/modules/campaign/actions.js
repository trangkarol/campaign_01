/* ============
 * Actions for the account module
 * ============
 *
 * The actions that are available on the
 * account module.
 */

import * as types from './mutation-types';
import { get, post } from '../../../helpers/api'

export const campaignDetail = ({ commit }, campaignId) => {
    get('campaign/' + campaignId)
        .then(res => {
            commit(types.CAMPAIGN_DETAIL, res.data)
        })
};

export const fetchData = ({ commit }, data) => {
    commit(types.LOADING, true)

    if (data.pageNumberEvent > (parseInt(data.events.length) / 15) + 1) {
        get('campaign/' + data.campaignId + '/timeline/event?page=' + ((parseInt(data.events.length) / 15) + 1))
            .then(res => {
                commit(types.FETCH_DATA, res.data.events.data)
                commit(types.LOADING, false)
            })
    } else {
        commit(types.LOADING, false)
    }
};

export const joinCampaign = ({ commit }, campaignId) => {
    return new Promise((resolve, reject) => {
        post('campaign/join-campaign/' + campaignId)
            .then(res => {
                if (res.data.http_status.status) {
                    console.log(res.data.join_campaign)
                    commit(types.JOIN_CAMPAIGN, res.data.join_campaign)
                    resolve(res.data.http_status.status)
                }
            })
            .catch(err => {
                reject(err)
            })
    })
};

export const leaveCampaign = ({ commit }, campaignId) => {
    console.log('campaign/leave-campaign/' + campaignId)
    return new Promise((resolve, reject) => {
        post('campaign/leave-campaign/' + campaignId)
            .then(res => {
                if (res.data.http_status.status) {
                    commit(types.LEAVE_CAMPAIGN, res.data.leave_campaign)
                    resolve(res.data.http_status.status)
                }
            })
            .catch(err => {
                reject(err)
            })
    })
};

export default {
    campaignDetail,
    fetchData,
    joinCampaign,
    leaveCampaign
};
