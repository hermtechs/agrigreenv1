import { destroyAuthentication, handleAPIResponse } from '../helpers/api';

const { dispatch } = wp.data;

import { getData } from '../helpers/data';
import popupWindow from '../helpers/popup';
import { dispatch as dispatchEvent } from '../helpers/event';
import {
	postAuthenticationRequest,
	postSettingsRequest,
} from '../helpers/endpoints';

export default function initializeAuthentication() {
	const { setIsAuthenticated, setSelectedPostTypes } = dispatch(
		'wordproof'
	);
	const authenticationLink = getData( 'popup_redirect_authentication_url' );
	const settingsLink = getData( 'popup_redirect_settings_url' );

	let popup = null;

	/**
	 * Open the settings popup.
	 *
	 * @param {Event} event
	 */
	const openSettings = ( event ) => {
		event.preventDefault();
		openPopup( settingsLink, 'WordProof_Settings' );
	};

	/**
	 * Open the authentication popup.
	 *
	 * @param {Event} event
	 */
	const openAuthentication = ( event ) => {
		event.preventDefault();
		openPopup( authenticationLink, 'WordProof_Authentication' );
	};

	/**
	 * Opens popup and set in state.
	 *
	 * @param {string} link
	 * @param {string} name
	 */
	const openPopup = ( link, name ) => {
		popup = popupWindow( window, link, name );

		if ( popup ) {
			popup.focus();
		}

		window.addEventListener( 'message', onPostMessage, false );
	};

	/**
	 * Listens to message events from the WordProof popup.
	 *
	 * @param {event} event The message event.
	 *
	 * @return {void}
	 */
	const onPostMessage = async ( event ) => {
		const { data, source, origin } = event;

		if ( origin !== getData( 'origin' ) || popup !== source ) {
			return;
		}

		switch ( data.type ) {
			case 'wordproof:oauth:granted':
				if (
					( await performAuthenticationRequest( data ) ) === false
				) {
					await postMessageResult( 'wordproof:oauth:failed', false );
				}
				break;
			case 'wordproof:oauth:failed':
				await postMessageResult( 'wordproof:oauth:failed', false );
				break;
			case 'wordproof:oauth:denied':
				await postMessageResult( 'wordproof:oauth:denied', false );
				break;
			case 'wordproof:webhook:success':
				await postMessageResult( 'wordproof:oauth:success', true );
				break;
			case 'wordproof:webhook:failed':
				await postMessageResult( 'wordproof:webhook:failed', false );
				break;
			case 'wordproof:settings:updated':
				await postMessageResult( 'wordproof:settings:updated' );
				await performSettingsRequest( data );
				break;
			case 'wordproof:oauth:destroy':
				await postMessageResult( 'wordproof:oauth:destroy', false );
				break;
			case 'wordproof:oauth:retry':
				await postMessageResult(
					'wordproof:open_authentication',
					false
				);
				break;
			case 'wordproof:oauth:close':
				closeModal();
				break;
		}
	};

	const postMessageResult = async ( event, isAuthenticated = null ) => {
		closeModal();
		dispatchEvent( event );

		if ( isAuthenticated === false ) {
			await destroyAuthentication();
			setIsAuthenticated( false );
		}

		if ( isAuthenticated === true ) {
			setIsAuthenticated( true );
		}
	};

	const closeModal = () => {
		window.removeEventListener( 'message', onPostMessage, false );
		popup.close();
	};

	const performAuthenticationRequest = async ( data ) => {
		await handleAPIResponse(
			() => postAuthenticationRequest( data ),
			async ( response ) => {
				const message = {
					type: 'wordproof:sdk:access-token',
					source_id: response.source_id,
				};
				popup.postMessage( message, getData( 'origin' ) );

				return true;
			},
			async () => {
				return false;
			}
		);
	};

	const performSettingsRequest = async ( data ) => {
		await handleAPIResponse(
			() => postSettingsRequest( data ),
			async () => {
				const settings = data.settings;

				if ( settings.selectedPostTypes ) {
					setSelectedPostTypes( settings.selectedPostTypes );
				}

				return true;
			},
			async () => {
				return false;
			}
		);
	};

	// Open the authentication and settings popup from other parts in the application.
	window.addEventListener(
		'wordproof:open_authentication',
		openAuthentication,
		false
	);

	window.addEventListener( 'wordproof:open_settings', openSettings, false );
}
