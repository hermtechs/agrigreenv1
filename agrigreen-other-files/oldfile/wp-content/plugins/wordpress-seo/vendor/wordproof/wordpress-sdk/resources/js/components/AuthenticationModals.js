const { useState, useCallback, useEffect } = wp.element;

import OauthDeniedContent from './modals/OauthDeniedContent';
import OauthFailedContent from './modals/OauthFailedContent';
import OauthSuccessContent from './modals/OauthSuccessContent';
import WebhookFailedContent from './modals/WebhookFailedContent';

const AuthenticationModals = () => {
	const [ modal, setModal ] = useState( null );

	/**
	 * Show oauth failed content.
	 *
	 * @return {void} Returns no value.
	 */
	const setOauthFailed = useCallback( () => {
		setModal( 'oauth:failed' );
	} );

	/**
	 * Show oauth denied content.
	 *
	 * @return {void} Returns no value.
	 */
	const setOauthDenied = useCallback( () => {
		setModal( 'oauth:denied' );
	} );

	/**
	 * Show oauth webhook failed content.
	 *
	 * @return {void} Returns no value.
	 */
	const setWebhookFailed = useCallback( () => {
		setModal( 'webhook:failed' );
	} );

	/**
	 * Show oauth success content.
	 *
	 * @return {void} Returns no value.
	 */
	const setOauthSuccess = useCallback( () => {
		setModal( 'oauth:success' );
	} );

	/**
	 * Stop displaying the current modal.
	 *
	 * @return {void} Returns no value.
	 */
	const closeModal = useCallback( () => {
		setModal( null );
	} );

	useEffect( () => {
		window.addEventListener(
			'wordproof:oauth:success',
			setOauthSuccess,
			false
		);

		window.addEventListener(
			'wordproof:oauth:failed',
			setOauthFailed,
			false
		);

		window.addEventListener(
			'wordproof:oauth:denied',
			setOauthDenied,
			false
		);

		window.addEventListener(
			'wordproof:webhook:failed',
			setWebhookFailed,
			false
		);

		return () => {
			window.removeEventListener(
				'wordproof:oauth:success',
				setOauthSuccess,
				false
			);

			window.removeEventListener(
				'wordproof:oauth:failed',
				setOauthFailed,
				false
			);

			window.removeEventListener(
				'wordproof:oauth:denied',
				setOauthDenied,
				false
			);

			window.removeEventListener(
				'wordproof:webhook:failed',
				setWebhookFailed,
				false
			);
		};
	}, [] );

	return (
		<>
			{ modal === 'oauth:success' && (
				<OauthSuccessContent close={ closeModal } />
			) }

			{ modal === 'oauth:denied' && (
				<OauthDeniedContent close={ closeModal } />
			) }

			{ modal === 'oauth:failed' && (
				<OauthFailedContent close={ closeModal } />
			) }

			{ modal === 'webhook:failed' && (
				<WebhookFailedContent close={ closeModal } />
			) }
		</>
	);
};

export default AuthenticationModals;
