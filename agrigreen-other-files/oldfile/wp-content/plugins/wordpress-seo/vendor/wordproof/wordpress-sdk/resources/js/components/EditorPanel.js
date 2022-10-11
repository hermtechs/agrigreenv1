import ActionLink from './ActionLink';
import AuthenticationModals from './AuthenticationModals';

const { __, sprintf } = wp.i18n;
const { PluginDocumentSettingPanel } = wp.editPost;
const { ToggleControl, PanelRow } = wp.components;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { useCallback } = wp.element;
import PropTypes from 'prop-types';
import { dispatch as dispatchEvent } from '../helpers/event';

const EditorPanel = ( {
	postType,
	postMeta,
	isAuthenticated,
	selectedPostTypes,
	setPostMeta,
} ) => {
	const timestampedAutomatically = useCallback( () => {
		return selectedPostTypes.includes( postType );
	}, [ selectedPostTypes, postType ] );

	const openAuthentication = useCallback( () => {
        dispatchEvent( 'wordproof:open_authentication' );
	} );

	return (
		<PluginDocumentSettingPanel
			title={ __( 'WordProof Timestamp', 'wordproof' ) }
			initialOpen="true"
		>
			<PanelRow>
				<ToggleControl
					label={ sprintf(
						/* translators: %s expands to the post type */
						__( 'Timestamp this %s', 'wordproof' ),
						postType
					) }
					onChange={ ( value ) => {
						setPostMeta( { _wordproof_timestamp: value } );

						if ( ! isAuthenticated && value === true ) {
							openAuthentication();
						}
					} }
					checked={
						postMeta._wordproof_timestamp ||
						timestampedAutomatically()
					}
					disabled={ timestampedAutomatically() }
				/>
			</PanelRow>
			<PanelRow>
				<ActionLink />
			</PanelRow>
			<AuthenticationModals />
		</PluginDocumentSettingPanel>
	);
};

EditorPanel.proptypes = {
	postType: PropTypes.string.isRequired,
	postMeta: PropTypes.object.isRequired,
	isAuthenticated: PropTypes.bool.isRequired,
	setPostMeta: PropTypes.func.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		return {
			postMeta: select( 'core/editor' ).getEditedPostAttribute( 'meta' ),
			postType: select( 'core/editor' ).getCurrentPostType(),
			isAuthenticated: select( 'wordproof' ).getIsAuthenticated(),
			selectedPostTypes: select( 'wordproof' ).getSelectedPostTypes(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setPostMeta( newMeta ) {
				dispatch( 'core/editor' ).editPost( { meta: newMeta } );
			},
		};
	} ),
] )( EditorPanel );
