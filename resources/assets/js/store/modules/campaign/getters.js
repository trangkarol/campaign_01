/* ============
 * Getters for the account module
 * ============
 *
 * The getters that are available on the
 * account module.
 */
export default {
    checkPermission(state, getters) {
        let check = true

        if (jQuery.isEmptyObject(state.campaign.check_owner) && jQuery.isEmptyObject(state.campaign.check_moderators)) {
            check = false
        }

        return check
    },

    checkJoinCampaign(state, getters) {

        if (jQuery.isEmptyObject(state.campaign.check_status)) {
            return 1 //join campaign
        }

        if (state.campaign.check_status.pivot.status == 0) {
            return 2 //approving
        }

        return 3 //approved
    }
};
