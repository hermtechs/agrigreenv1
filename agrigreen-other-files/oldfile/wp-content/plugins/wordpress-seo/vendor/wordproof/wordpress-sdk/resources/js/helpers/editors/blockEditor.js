const { subscribe, select } = wp.data;

/**
 * Executes callback on post editor save.
 *
 * @param {Function} callback The callback.
 *
 * @return {void}
 */
export function callbackOnSave( callback ) {
	let firstTime = true;

	subscribe( () => {
		const isSavingPost = select( 'core/editor' ).isSavingPost();
		const isAutosavingPost = select( 'core/editor' ).isAutosavingPost();
		const didPostSaveRequestSucceed = select(
			'core/editor'
		).didPostSaveRequestSucceed();

		if ( isSavingPost && didPostSaveRequestSucceed && ! isAutosavingPost ) {
			if ( firstTime ) {
				firstTime = false;
				return;
			}

			callback();
		}
	} );
}
