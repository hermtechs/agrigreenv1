import { getData } from '../helpers/data';
import { dispatch } from '../helpers/event';

const { __ } = wp.i18n;
const { useCallback } = wp.element;
const { withSelect } = wp.data;
const { compose } = wp.compose;
import PropTypes from 'prop-types';

const ActionLink = ( props ) => {
	const { isAuthenticated } = props;

	const authenticationLink = getData( 'popup_redirect_authentication_url' );
	const settingsLink = getData( 'popup_redirect_settings_url' );

	const openSettings = useCallback( ( event ) => {
		event.preventDefault();
		dispatch( 'wordproof:open_settings' );
	} );

	const openAuthentication = useCallback( ( event ) => {
		event.preventDefault();
		dispatch( 'wordproof:open_authentication' );
	} );

	return (
		<>
			{ isAuthenticated && (
				<a href={ settingsLink } onClick={ openSettings }>
					{ __( 'Open settings', 'wordproof' ) }
				</a>
			) }

			{ ! isAuthenticated && (
				<a href={ authenticationLink } onClick={ openAuthentication }>
					{ __( 'Open authentication', 'wordproof' ) }
				</a>
			) }
		</>
	);
};

ActionLink.proptypes = {
	isAuthenticated: PropTypes.bool.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		return {
			isAuthenticated: select( 'wordproof' ).getIsAuthenticated(),
		};
	} ),
] )( ActionLink );
