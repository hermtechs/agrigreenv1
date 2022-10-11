import initializeTimestamper from './timestamper';
import initializeAuthentication from './authentication';
import {
	callbackOnSave,
	createNotice,
} from '../helpers/editors/elementorEditor';

/**
 * Initializes the WordProof integration.
 *
 * @return {void}
 */
export default function initializeElementorEditor() {
	initializeAuthentication();

	initializeTimestamper( callbackOnSave, createNotice, createNotice );
}
