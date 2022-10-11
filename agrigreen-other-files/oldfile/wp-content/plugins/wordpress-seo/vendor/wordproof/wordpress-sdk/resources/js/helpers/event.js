/**
 * Dispatch custom event helper.
 *
 * @param {string} name
 */
export function dispatch( name ) {
	const event = new window.CustomEvent( name );
	window.dispatchEvent( event );
}
