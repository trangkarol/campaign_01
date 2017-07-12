import * as types from './mutation-types'
import { get, post } from 'axios'

export default {
    [types.CHANGE_COMMENT](state, data) {
        console.log('commendetail', data)
        state.comments[data.modelId] = []

        data.comments.data.forEach(function(item, index) {
            state.comments[item.commentable_id][index] = item
        })

        state.paginates[data.modelId] = {
            total: data.comments.total,
            last_page: data.comments.last_page,
            page_current: data.comments.current_page
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

        if (comments.length > 0 ) {
            let exits = $.grep(comments, function(item, index) {
                    return index == data.modelId;
            });

            if (exits.length <= 0) {
                    comments[data.modelId] = data.comments
            } else {
                if (data.flagAction == 'edit') {
                    let dataComment = $.map(comments[data.modelId], function(item, index) {
                        if (item.id == data.comments.id) {
                            return data.comments;
                        }

                        return item;
                    });

                    comments[data.modelId] = []
                    comments[data.modelId] = dataComment
                } else {
                    comments[data.modelId] = comments[data.modelId].concat(data.comments)
                }
            }
        }

        state.comments = []
        state.comments = comments
    },

    [types.SUB_COMMENT](state, data) {
        var comments = state.comments

        if (comments.length > 0 ) {
            let index = $.map(comments[data.modelId], function(item, index) {
                if (item.id == data.comments.parent_id) {
                    return index;
                }
            });

            if (index === null) {
                comments[data.modelId][index[0]].sub_comment.data = []
            }

            if (data.flagAction == 'edit') {
                let dataComment = $.map(comments[data.modelId][index[0]].sub_comment.data, function(item, index) {
                    if (item.id == data.comments.id) {
                        return data.comments;
                    }

                    return item;
                });

                comments[data.modelId][index[0]].sub_comment.data = []
                comments[data.modelId][index[0]].sub_comment.data = dataComment
            } else {
                console.log(comments[data.modelId])
               comments[data.modelId][index[0]].sub_comment.data = comments[data.modelId][index[0]].sub_comment.data.concat(data.comments)
            }
        }

        state.comments = []
        state.comments = comments
    },

    [types.DELETE_PARENT_COMMENT](state, data) {
        var comments = state.comments
        let dataComment = $.grep(comments[data.modelId], function(item, index) {
                return item.id !==  data.commentId;
        });

        comments[data.modelId] = []
        comments[data.modelId] = dataComment

        state.comments = []
        state.comments = comments
    },

    [types.DELETE_SUB_COMMENT](state, data) {
        var comments = state.comments
        let index = $.map(comments[data.modelId], function(item, index) {
            if (item.id == data.commentParentId) {
                return index;
            }
        });

        let dataComment = $.grep(comments[data.modelId][index[0]].sub_comment.data, function(item, index) {
                return item.id !==  data.commentId;
        });

        comments[data.modelId][index[0]].sub_comment.data = []
        comments[data.modelId][index[0]].sub_comment.data = dataComment

        state.comments = []
        state.comments = comments
    },

    [types.LOAD_MORE_PARENT_COMMENT](state, data) {
        var comments = state.comments
        var paginates = state.paginates
        // comment
        comments[data.modelId] = data.comments.concat(comments[data.modelId])
        state.comments = []
        state.comments = comments
        //page
        paginates[data.modelId].page_current = paginates[data.modelId].page_current + 1;
        state.paginates = paginates
    },

    [types.LOAD_MORE_SUB_COMMENT](state, data) {
        var comments = state.comments
        let index = $.map(comments[data.modelId], function(item, index) {
            console.log('sub comment', item)

            if (item.id == data.commentParentId) {
                return index;
            }
        });

        console.log('index', index)

        // comment
        comments[data.modelId][index[0]].sub_comment = data.comments.data.concat(comments[data.modelId][index[0]].sub_comment.data)


        state.comments = []
        state.comments = comments

        console.log('result', data.modelId, state.comments)
        //page
    },
};
