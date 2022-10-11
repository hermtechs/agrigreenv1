import initializeAuthentication from './authentication';

/**
 * Initializes the WordProof integration.
 *
 * @return {void}
 */
export default function initializeClassicEditor() {
	initializeAuthentication();
}
