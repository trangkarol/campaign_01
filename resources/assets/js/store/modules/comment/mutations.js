import * as types from './mutation-types'
import { post, get } from 'axios'

export default {
    [types.CHANGE_COMMENT](state, data) {
        if (typeof (state.comments[data.flag]) === "undefined") {
            state.comments[data.flag] = []
            state.paginates[data.flag] = []
        }

        state.comments[data.flag][data.modelId] = []
        var commentReverse = data.comments.reverse()

        commentReverse.forEach(function (item, index) {
            state.comments[data.flag][item.commentable_id][index] = item
            state.comments[data.flag][item.commentable_id][index]['sub_comment']['data'] = item.sub_comment.data.reverse()
            state.comments[data.flag][item.commentable_id][index]['sub_comment']['last_page'] = 2
            state.comments[data.flag][item.commentable_id][index]['sub_comment']['total'] = item.number_of_comments
            state.comments[data.flag][item.commentable_id][index]['sub_comment']['current_page'] = 1
        })

        state.paginates[data.flag][data.modelId] = {
            total: data.numberOfComments,
            last_page: 2,
            current_page: 1
        }
    },

    [types.COMMENT_DETAIL](state, data) {
        let comments = state.comments
        comments[data.modelId] = data.comments
        state.comments = []
        state.comments = comments
    },

    [types.PARENT_COMMENT](state, data) {
        var comments = state.comments

        let exits = $.grep(comments[data.flag], function (item, index) {
            return index == data.modelId;
        });

        if (exits.length <= 0) {
            comments[data.flag][data.modelId] = data.comments
        } else {
            if (data.flagAction == 'edit') {
                let dataComment = $.map(comments[data.flag][data.modelId], function (item, index) {
                    if (item.id == data.comments.id) {
                        return data.comments;
                    }

                    return item;
                });

                comments[data.flag][data.modelId] = []
                comments[data.flag][data.modelId] = dataComment
            } else {
                data.comments['sub_comment'] = []
                data.comments['sub_comment'].data = []
                data.comments['sub_comment']['last_page'] = 1
                data.comments['sub_comment']['current_page'] = 0
                data.comments['sub_comment']['total'] = 0

                comments[data.flag][data.modelId] = comments[data.flag][data.modelId].concat(data.comments)
            }
        }

        state.comments = []
        state.comments = comments

        //change comment total at model
        let commentTotal = data.rootStateLike.like.commentTotal
        commentTotal[data.flag][data.modelId] = data.numberComment
        data.rootStateLike.like.commentTotal = []
        data.rootStateLike.like.commentTotal = commentTotal
    },

    [types.SUB_COMMENT](state, data) {
        var comments = state.comments
        let index = $.map(comments[data.flag][data.modelId], function (item, index) {
            if (item.id == data.comments.parent_id) {
                return index;
            }
        });

        if (index === null) {
            comments[data.flag][data.modelId][index[0]].sub_comment.data = []
        }

        if (data.flagAction == 'edit') {
            let dataComment = $.map(comments[data.flag][data.modelId][index[0]].sub_comment.data, function (item, index) {
                if (item.id == data.comments.id) {
                    return data.comments;
                }

                return item;
            });

            comments[data.flag][data.modelId][index[0]].sub_comment.data = []
            comments[data.flag][data.modelId][index[0]].sub_comment.data = dataComment
            comments[data.flag][data.modelId][index[0]].number_of_comments = data.numberComment
        } else {
            comments[data.flag][data.modelId][index[0]].number_of_comments = data.numberComment
            comments[data.flag][data.modelId][index[0]].sub_comment.data = comments[data.flag][data.modelId][index[0]].sub_comment.data.concat(data.comments)
        }

        state.comments = []
        state.comments = comments
    },

    [types.DELETE_PARENT_COMMENT](state, data) {
        var comments = state.comments
        let dataComment = $.grep(comments[data.flag][data.modelId], function (item, index) {
            return item.id !== data.commentId;
        });

        comments[data.flag][data.modelId] = []
        comments[data.flag][data.modelId] = dataComment

        state.comments = []
        state.comments = comments

        var paginates = state.paginates
        paginates[data.flag][data.modelId].current_page -= 1

        state.paginates = []
        state.paginates = paginates

        //change comment total at model
        let commentTotal = data.rootStateLike.like.commentTotal
        commentTotal[data.flag][data.modelId] = data.numberComment
        data.rootStateLike.like.commentTotal = []
        data.rootStateLike.like.commentTotal = commentTotal
    },

    [types.DELETE_SUB_COMMENT](state, data) {
        var comments = state.comments
        let index = $.map(comments[data.flag][data.modelId], function (item, index) {
            if (item.id == data.commentParentId) {
                return index;
            }
        });

        let dataComment = $.grep(comments[data.flag][data.modelId][index[0]].sub_comment.data, function (item, index) {
            return item.id !== data.commentId;
        });

        comments[data.flag][data.modelId][index[0]].number_of_comments = data.numberComment
        comments[data.flag][data.modelId][index[0]].sub_comment.data = []
        comments[data.flag][data.modelId][index[0]].sub_comment.data = dataComment

        state.comments = []
        state.comments = comments
    },

    [types.LOAD_MORE_PARENT_COMMENT](state, data) {
        var comments = state.comments
        var paginates = state.paginates
        var commentReverse = data.comments.data.reverse()

        let like = data.rootStateLike.like;
        for (let comment of data.comments.data) {
            like.like['comment'][comment.id] = comment.likes
            like.checkLike['comment'][comment.id] = comment.checkLike
            like.like['comment'][comment.id]['numberOfLikes'] = comment.number_of_likes
        }

        data.rootStateLike.like = []
        data.rootStateLike.like = like
            // comment
        commentReverse.forEach(function (item, index) {
            commentReverse[index]['sub_comment']['data'] = item.sub_comment.data.reverse()
            commentReverse[index]['sub_comment']['last_page'] = 2
            commentReverse[index]['sub_comment']['total'] = item.number_of_comments
            commentReverse[index]['sub_comment']['current_page'] = 1
        })

        comments[data.flag][data.modelId] = [...commentReverse, ...comments[data.flag][data.modelId]]

        state.comments = []
        state.comments = comments

        //page
        paginates[data.flag][data.modelId].current_page = data.comments.current_page
        paginates[data.flag][data.modelId].last_page = data.comments.last_page
        state.paginates = paginates
    },

    [types.LOAD_MORE_SUB_COMMENT](state, data) {
        var comments = state.comments
        let index = $.map(comments[data.flag][data.modelId], function (item, index) {
            if (item.id == data.commentParentId) {
                return index;
            }
        });

        // comment
        data.comments.data = data.comments.data.concat(comments[data.flag][data.modelId][index[0]].sub_comment.data)
        comments[data.flag][data.modelId][index[0]].sub_comment = data.comments

        let like = data.rootStateLike.like

        for (let subComment of data.comments.data) {
            like.like['comment'][subComment.id] = subComment.likes
            like.checkLike['comment'][subComment.id] = subComment.checkLike
            like.like['comment'][subComment.id]['numberOfLikes'] = subComment.number_of_likes
        }

        data.rootStateLike.like = []
        data.rootStateLike.like = like

        state.comments = []
        state.comments = comments
    },
};
