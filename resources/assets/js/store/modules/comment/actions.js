/* ============
 * Actions for the account module
 * ============
 *
 * The actions that are available on the
 * account module.
 */

import * as types from './mutation-types';
import {get } from '../../../helpers/api'

export const getComment = ({ commit }, data) => {
    commit(types.GET_COMMENT, data)
};

export const changeComment = ({ commit }, data) => {
    commit(types.CHANGE_COMMENT, data)
};

export const commentDetail = ({ commit }, modelId) => {

    get('comment/' + modelId)
        .then(res => {
            if (res.data.http_status.status) {
                commit(types.COMMENT_DETAIL, { comments: res.data.comment.data, modelId: modelId })
            }
        })
        .catch(err => {
            console.log(err)
                // this.spinner = false
                // const message = this.$i18n.t('messages.regiser_fail')
                // noty({ text: message, force: true})
        })
};

export default {
    getComment,
    commentDetail,
    changeComment
};