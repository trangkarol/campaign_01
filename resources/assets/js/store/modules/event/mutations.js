import * as types from './mutation-types';
import axios from 'axios'

export default {
    [types.SET_EVENT](state, dataEvent) {
        state.event = dataEvent.event[0]
        state.event.complete_percent = dataEvent.goals
        state.event.manage = dataEvent.manage
        state.event.isMember = dataEvent.member
        state.actions = dataEvent.actions
        state.dataGoals = dataEvent.goals
        state.checkLiked = dataEvent.checkLikeEvent
    },

    [types.LOAD_ACTION](state, dataAction) {
        state.actions.checkLikeAction = $.merge(state.actions.checkLikeAction, dataAction.actions.checkLikeAction)
        let old_data = state.actions.list_action.data
        state.actions.list_action = dataAction.actions.list_action
        state.actions.list_action.data = $.merge(old_data, state.actions.list_action.data)
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
        state.actions.list_action.data = $.grep(state.actions.list_action.data, function (item, index) {
            return item.id !== actionId
        });
    },

    [types.APPEND_ONE_ACTION](state, data) {
        if (typeof (data.rootStateLike.like['action']) === "undefined") {
            data.rootStateLike.like['action'] = []
        }

        data.rootStateLike.like['action'][data.action.list_action.id] = []
        data.rootStateLike.checkLike['action'][data.action.list_action.id] = false
        data.rootStateLike.like['action'][data.action.list_action.id]['numberOfLikes'] = 0
        state.actions.list_action.data = [data.action.list_action, ...state.actions.list_action.data]
        state.actions.checkLikeAction = []
    },

    [types.UPDATE_ACTION](state, data) {
        let actions = state.actions

        actions.list_action.data.forEach(function (item, index) {
            if (item.id == data.id) {
                actions.list_action.data[index] = data;
                actions.list_action.data[index].likes = item.likes;
                actions.list_action.data[index].user = item.user;
            }
        })

        state.actions = []
        state.actions = actions
    }
};
