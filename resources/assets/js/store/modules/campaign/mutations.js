import { CAMPAIGN_DETAIL, FETCH_DATA, LOADING } from './mutation-types'
import {get, post } from 'axios'

export default {
    [CAMPAIGN_DETAIL](state, data) {
        state.campaign = data.show_campaign.campaign
        state.events = data.events.data
        state.tags = data.show_campaign.tags
    },

    [FETCH_DATA](state, events) {
        state.events = [...state.events, ...events]
    },

    [LOADING](state, data) {
        state.loading = data
    }
};