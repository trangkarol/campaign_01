import * as types from './mutation-types'

export default {
    [types.SET_LIKE](state, data) {
        if (typeof(state.like[data.flag]) === "undefined") {
            state.like[data.flag] = []
            state.commentTotal[data.flag] = []
            state.checkLike[data.flag] = []
        }

        state.like[data.flag][data.modelId] = []

        if (typeof(data.likes) !== "undefined") {
            data.likes.forEach(function (item, index) {
                state.like[data.flag][data.modelId][index] = item
            })
        }

        //check user liked in model
        if (data.flag != 'comment') {
            state.checkLike[data.flag][data.modelId] = false

            if (data.checkLike != null) {
                data.checkLike.forEach(function (item, index) {
                    if (item == data.modelId) {
                        state.checkLike[data.flag][data.modelId] = true
                    }
                });
            }
        } else {
            state.checkLike[data.flag][data.modelId] = data.checkLike
        }

        state.like[data.flag][data.modelId]['numberOfLikes'] = data.numberOfLikes
        state.commentTotal[data.flag][data.modelId] = data.numberOfComments
    },

    [types.LIKE_ACTIVITY](state, data) {
        var like = state.like
        let checkLike = state.checkLike

        if (typeof (data.like) == 'boolean') {
            let dataLike = $.grep(like[data.flag][data.modelId], function (item, index) {
                return item.user_id !== data.user.id
            });

            like[data.flag][data.modelId] = dataLike
            checkLike[data.flag][data.modelId] = false
        } else {
            like[data.flag][data.modelId] = like[data.flag][data.modelId].concat(data.like)
            checkLike[data.flag][data.modelId] = true
        }

        like[data.flag][data.modelId].numberOfLikes = data.numberOfLikes
        state.like = []
        state.like = like

        state.checkLike = []
        state.checkLike = checkLike
    },

    [types.LIST_MEMBERS](state, data) {
        if (typeof(state.listMember[data.flag]) === "undefined") {
            state.listMember[data.flag] = []
        }

        if (data.member.current_page >= 2) {
            data.member.data = state.listMember[data.flag][data.modelId].data.concat(data.member.data)
        }

        state.listMember[data.flag][data.modelId] = []
        state.listMember[data.flag][data.modelId] = data.member
    },
};
