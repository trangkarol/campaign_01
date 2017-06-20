/* ============
 * Getters for the account module
 * ============
 *
 * The getters that are available on the
 * account module.
 */
export default {
    getDetailedComment: state => (id) => {
        console.log('getter', id)
        return state.comments[id]
    }
};