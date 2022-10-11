import apiFetch from '@wordpress/api-fetch';
import {
	destroyAuthenticationRequest,
	getIsAuthenticatedRequest,
	getSettingsRequest,
} from './endpoints';

/**
 * Wraps the API requests and handles the API responses.
 *
 * @param {Function} apiRequest         The API request function call to handle.
 * @param {Function} onSuccessCallback  The callback to run on a successful response.
 * @param {Function} onFailureCallback  The callback to run on a failed response.
 * @param {number}   expectedStatusCode The expected status code to run the success callback on.
 *
 * @return {Promise} The handled response promise.
 */
export async function handleAPIResponse(
	apiRequest,
	onSuccessCallback,
	onFailureCallback,
	expectedStatusCode = 200
) {
	try {
		const response = await apiRequest();

		// No response if the request was aborted.
		if ( response ) {
			if ( response.status === expectedStatusCode ) {
				return onSuccessCallback( response );
			}

			return onFailureCallback( response );
		}

		return false;
	} catch ( e ) {}
}

/**
 * Calls the passed endpoint and handles any potential errors.
 *
 * @param {Object} endpoint The endpoint object.
 *
 * @return {Promise} The API response promise.
 */
export async function callEndpoint( endpoint ) {
	try {
		return await apiFetch( endpoint );
	} catch ( e ) {
		// If the error object looks like what we expect, return it.
		if ( e.error && e.status ) {
			return e;
		}

		// Sometimes we get a Response instance back instead of the data itself.
		if ( e instanceof window.Response ) {
			return await e.json();
		}

		// Likely AbortError, otherwise a connection error.
		// We need to somehow upgrade @wordpress/api-fetch to differentiate between these.
		return false;
	}
}

/**
 * Destroy oauth token
 *
 * @return {Promise<Object|boolean>} The response object or false if request fails.
 */
export const destroyAuthentication = async () => {
	return await handleAPIResponse(
		async () => await destroyAuthenticationRequest(),
		( response ) => response,
		() => false
	);
};

/**
 * Retrieves WordProof settings.
 *
 * @return {Promise<Object|boolean>} The response object or false if request fails.
 */
export const fetchSettings = async () => {
	return await handleAPIResponse(
		async () => await getSettingsRequest(),
		( settings ) => settings,
		() => false
	);
};

/**
 * Retrieves WordProof authentication status.
 *
 * @return {Promise<boolean>} The authentication status.
 */
export const fetchIsAuthenticated = async () => {
	return await handleAPIResponse(
		async () => await getIsAuthenticatedRequest(),
		// eslint-disable-next-line camelcase
		( { is_authenticated } ) => is_authenticated,
		() => false
	);
};
