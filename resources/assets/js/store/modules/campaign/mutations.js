import * as types from './mutation-types'

export default {
    [types.CAMPAIGN_DETAIL](state, data) {
        state.campaign = data.show_campaign.campaign
        state.listMembers.members = data.members
        state.events = data.events
        state.checkLiked = data.checkLiked
        state.pageNumberEvent = data.events.last_page
        state.pageCurrentEvent = data.events.current_page
        state.tags = data.show_campaign.tags
    },

    [types.FETCH_DATA](state, events) {
        let dataEvents = state.events
        events.data = [...dataEvents.data, ...events.data]
        state.events = events
    },

    [types.LOADING](state, data) {
        state.loading = data
    },

    [types.JOIN_CAMPAIGN](state, data) {
        let campaign = state.campaign
        data.pivot = []
        data.pivot.status = 0
        campaign.check_status = data
        state.campaign = []
        state.campaign = campaign
    },

    [types.LEAVE_CAMPAIGN](state, data) {
        let list_members = state.listMembers
        list_members.members.data = $.grep(list_members.members.data, function (user) {
            return user.id != data.id;
        });

        list_members.members.total -= 1
        state.listMembers = list_members

        //set status members
        let campaign = state.campaign
        campaign.check_status = null
        state.campaign = []
        state.campaign = campaign
    },

    [types.LIST_PHOTOS](state, listPhotos) {
        state.listPhotos = listPhotos
    },

    [types.APPROVE_JOIN_CAMPAIGN](state, data) {
        let list_members = state.listMembers
        let dataMember = list_members.members.data

        data.pivot = []
        data.pivot.status = 0
        data.pivot.campaign_id = state.campaign.id
        data.pivot.user_id = data.id

        list_members.members.data = []
        list_members.members.data = [...dataMember, ...data]
        list_members.members.total += 1

        state.listMembers = list_members
    },

    [types.BLOCKED_CAMPAIGN](state, userId) {
        let list_members = state.listMembers
        list_members.members.data = $.grep(list_members.members.data, function (user) {
            return user.id != userId;
        });
        list_members.members.total -= 1
        state.listMembers = list_members

        //set status members
        let campaign = state.campaign
        campaign.check_status = null
        state.campaign = []
        state.campaign = campaign
    },

    [types.LOAD_MORE_LIST_PHOTOS](state, listPhotos) {
        listPhotos.data = [...state.listPhotos.data, ...listPhotos.data]
        state.listPhotos = []
        state.listPhotos = listPhotos
    },

    [types.LOAD_MORE_MEMBERS](state, members) {
        members.data = [...state.listMembers.members.data, ...members.data]
        state.listMembers.members = []
        state.listMembers.members = members
    },

    [types.SET_DETAIL_CAMPAIGN](state, campaign) {
        state.detailCampaign[campaign.id] = campaign
    },
};
