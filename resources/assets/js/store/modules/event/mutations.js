import * as types from './mutation-types';
import axios from 'axios'

export default {
    [types.SET_EVENT](state, dataEvent) {
        state.event = dataEvent.event[0]
        state.event.complete_percent = dataEvent.goals
        state.event.manage = dataEvent.manage
        state.actions = dataEvent.actions
        state.dataGoals = dataEvent.goals
        state.checkLiked = dataEvent.checkLikeEvent
    },

    [types.LOAD_ACTION](state, dataAction) {
        let old_data = state.actions.data
        state.actions = dataAction.actions
        state.actions.data = $.merge(old_data, state.actions.data)
    },

    [types.SET_ACTION](state, dataAction) {
        state.actions = dataAction
    },

    [types.SET_KEY_SEARCH](state, dataKey) {
        state.key_search = dataKey
    },

    [types.SET_FLAG_SEARCH](state, dataFlag) {
        state.flag_search = dataFlag
    },

    [types.SET_LOAD_SEARCH](state, dataLoad) {
        state.load_search = dataLoad
    },

    [types.SET_LOAD_PAGINATE](state, dataLoad) {
        state.load_paginate = dataLoad
    },

    [types.SET_DETAIL_EVENT](state, event) {
        state.detailEvent[event.id] = event
    },

    [types.UPDATE_DONATION](state, data) {
        let event = state.event
        event.complete_percent = data
        state.event = []
        state.event = event
    },

    [types.CHANGE_DONATION_STATUS](state, data) {
        let event = state.event
        let i = event.complete_percent.findIndex(goal => goal.id == data.goal_id)
        let index = event.complete_percent[i].donations.findIndex(donation => donation.id == data.id)
        event.complete_percent[i].donations.splice(index, 1, data)
        state.event = []
        state.event = event
    },

    [types.REMOVE_ACTION](state, actionId) {
        state.actions.data = $.grep(state.actions.data, function (item, index) {
            return item.id !== actionId
        });
    }
};
