import * as types from './mutation-types'

export default {
    [types.CAMPAIGN_DETAIL](state, data) {
        state.campaign = data.show_campaign.campaign
        state.listMembers.members = data.show_campaign.campaign.members
        state.listMembers.memberIds = data.show_campaign.campaign.memberIds
        state.events = data.events.data
        state.pageNumberEvent = data.events.last_page
        state.tags = data.show_campaign.tags
    },

    [types.FETCH_DATA](state, events) {
        state.events = [...state.events, ...events]
    },

    [types.LOADING](state, data) {
        state.loading = data
    },

    [types.JOIN_CAMPAIGN](state, data) {
        let list_members = state.listMembers
        list_members.memberIds.push(data.id)
        list_members.members.push(data)
        state.listMembers = list_members
    },

    [types.LEAVE_CAMPAIGN](state, data) {
        let list_members = state.listMembers
        list_members.memberIds.splice($.inArray(data.id, list_members.memberIds),1);
        list_members.members = $.grep(list_members.members, function(user){
             return user.id != data.id;
        });

        state.listMembers = list_members
    }
};
