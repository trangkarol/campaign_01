/* ============
 * Getters for the account module
 * ============
 *
 * The getters that are available on the
 * account module.
 */
export default {
    campaign(state) {
        return state.campaign
    },
    events(state) {
        return state.events
    },
    tags(state) {
        return state.tags
    },
    loading(state) {
        return state.loading
    }
};