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

export const attendCampaign = ({ commit }, data) => {
    return new Promise((resolve, reject) => {
        post('campaign/attend-campaign/' + data.campaignId + '/' + data.flag)
            .then(res => {
                if (res.data.http_status.status) {
                    if (data.flag == 'join') {
                        commit(types.JOIN_CAMPAIGN, res.data.attend_campaign)
                        resolve(res.data.http_status.status)
                    } else {
                        commit(types.LEAVE_CAMPAIGN, res.data.attend_campaign)
                        resolve(res.data.http_status.status)
                    }

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
    attendCampaign,
};
