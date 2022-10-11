import { registerElementorDataHookAfter } from '../elementorHook';

/**
 * Creates an elementor notice.
 *
 * @param {string} content The message content.
 * @param {Object} options The notice options.
 *
 * @return {void}
 */
export function createNotice( content, options ) {
	let actions = null;

	if ( options.actions ) {
		actions = [];

		options.actions.forEach( ( button ) => {
			actions.push( {
				name: 'wordproof_notice_button',
				text: button.label,
				callback() {
					button.onClick();
				},
			} );
		} );
	}

	window.elementor.notifications.showToast( {
		message: content,
		buttons: actions,
	} );
}

/**
 * Executes callback on post editor save.
 *
 * @param {Function} callback The callback.
 *
 * @return {void}
 */
export function callbackOnSave( callback ) {
	registerElementorDataHookAfter(
		'document/save/save',
		'wordproof/timestamper',
		() => {
			/**
			 * The post meta is not consistently saved on this hook.
			 * Wait 1 second to send the timestamp request.
			 */
			window.setTimeout( callback, 1000 );
		}
	);
}
