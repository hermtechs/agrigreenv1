/**
 * Pause execution for a given amount of milliseconds.
 *
 * @param {number} time
 * @return {Promise<unknown>} The promise to wait for.
 */
export function wait( time ) {
	return new Promise( ( resolve ) => setTimeout( resolve, time ) );
}
