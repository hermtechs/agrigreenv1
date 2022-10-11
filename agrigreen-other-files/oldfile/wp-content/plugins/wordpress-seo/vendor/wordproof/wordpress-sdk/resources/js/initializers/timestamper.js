import { postTimestampRequest } from '../helpers/endpoints';
import { handleNoticesAfterTimestamp } from '../helpers/editors/editor';
import { getData } from '../helpers/data';

// eslint-disable-next-line no-undef
const { debounce } = lodash;
const { applyFilters } = wp.hooks;

/**
 * Initializes the timestamper.
 *
 * @param {Function} callbackOnSave      Function to register the timestamp callback
 * @param {Function} createSuccessNotice Function to display a success notice.
 * @param {Function} createErrorNotice   Function to display an error notice.
 * @return {void}
 */
export default function initializeTimestamper(
	callbackOnSave,
	createSuccessNotice,
	createErrorNotice
) {
	const sendTimestampRequest = debounce( async () => {
		if ( applyFilters( 'wordproof.timestamp', true ) ) {
			const postId = getData( 'current_post_id' );

			const response = await postTimestampRequest( postId );

			handleNoticesAfterTimestamp( {
				response,
				createSuccessNotice,
				createErrorNotice,
				postId,
			} );
		}
	}, 500 );

	callbackOnSave( sendTimestampRequest );
}
