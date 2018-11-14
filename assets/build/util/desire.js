/**
 * @export
 * @param {string} dependency
 * @param {any} [fallback]
 * @returns {any}
 */
module.exports = (dependency, fallback) => {
    try {
        require.resolve(dependency)
    } catch ( e ) {
        return fallback
    }
    return require(dependency) // eslint-disable-line import/no-dynamic-require
}