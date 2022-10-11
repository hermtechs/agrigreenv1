import { dispatch } from '@wordpress/data';

import initializeTimestamper from './timestamper';
import initializeAuthentication from './authentication';
import { callbackOnSave } from '../helpers/editors/blockEditor';

/**
 * Initializes the WordProof integration.
 *
 * @return {void}
 */
export default function initializeBlockEditor() {
	const { createSuccessNotice, createErrorNotice } = dispatch(
		'core/notices'
	);

	initializeAuthentication();

	initializeTimestamper(
		callbackOnSave,
		createSuccessNotice,
		createErrorNotice
	);
}
