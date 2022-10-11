import { getData } from '../data';
import PropTypes from 'prop-types';
import { getLatestTimestampTransactionRequest } from '../endpoints';
import { dispatch } from '../event';

export const isElementorEditor = () => {
	return getData( 'post_editor' ) === 'elementor';
};

export const isBlockEditor = () => {
	return getData( 'post_editor' ) === 'block';
};

export const isClassicEditor = () => {
	return getData( 'post_editor' ) === 'classic';
};

const handleNoticesAfterTimestamp = ( props ) => {
	const { response, createSuccessNotice, createErrorNotice, postId } = props;

	if ( response === null || response.status === 200 ) {
		return;
	}

	const successNoticeOptions = {
		type: 'snackbar',
		id: 'wordproof-timestamp-notice',
	};

	const errorNoticeOptions = { id: 'wordproof-timestamp-notice' };

	if ( response && response.status === 201 ) {
		if ( response.balance === 0 ) {
			errorNoticeOptions.actions = [
				{
					label: getData( 'translations.open_settings_button_text' ),
					onClick: () => {
						dispatch( 'wordproof:open_settings' );
					},
					variant: 'link',
				},
			];

			createErrorNotice(
				getData( 'translations.no_balance' ),
				errorNoticeOptions
			);
		} else {
			createSuccessNotice(
				getData( 'translations.timestamp_success' ),
				successNoticeOptions
			);
			checkForWebhook(
				postId,
				response.hash,
				createErrorNotice,
				errorNoticeOptions
			);
		}
	} else if ( response.error ) {
		switch ( response.error ) {
			case 'not_authenticated':
				errorNoticeOptions.type = 'snackbar';

				errorNoticeOptions.actions = [
					{
						label: getData(
							'translations.open_authentication_button_text'
						),
						onClick: () => {
							dispatch( 'wordproof:open_authentication' );
						},
						variant: 'link',
					},
				];

				createErrorNotice(
					getData( 'translations.not_authenticated' ),
					errorNoticeOptions
				);
				break;
			case 'timestamp_failed':
			default:
				createErrorNotice(
					getData( 'translations.timestamp_failed' ),
					errorNoticeOptions
				);
		}
	}
};

const checkForWebhook = async (
	postId,
	hash,
	createErrorNotice,
	errorNoticeOptions
) => {
	setTimeout( async () => {
		const transaction = await getLatestTimestampTransactionRequest(
			postId
		);

		if ( transaction.hash !== hash ) {
			errorNoticeOptions.type = 'snackbar';
			createErrorNotice(
				getData( 'translations.webhook_failed' ),
				errorNoticeOptions
			);
		}
	}, 10000 );
};

handleNoticesAfterTimestamp.proptypes = {
	timestampResponse: PropTypes.any.isRequired,
	createSuccessNotice: PropTypes.func.isRequired,
	createErrorNotice: PropTypes.func.isRequired,
	postId: PropTypes.number.isRequired,
};

export { handleNoticesAfterTimestamp };
