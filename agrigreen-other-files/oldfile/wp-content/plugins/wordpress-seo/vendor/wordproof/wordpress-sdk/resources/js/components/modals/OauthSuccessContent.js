import WordProofModal from './Modal';

const { __, sprintf } = wp.i18n;
const { compose } = wp.compose;
const { withSelect } = wp.data;
import PropTypes from 'prop-types';

const OauthSuccessContent = ( props ) => {
	const { close, postType } = props;

	return (
		<WordProofModal
			close={ close }
			title={ __( 'Authenticated', 'wordproof' ) }
		>
			<p>
				{ sprintf(
					/* translators: %1$s expands to WordProof. %2$s is the singular post type. */
					__(
						'You have successfully connected your %1$s account with this site. Your %2$s will now be timestamped everytime you update or publish.',
						'wordproof'
					),
					'WordProof',
					postType
				) }
			</p>
		</WordProofModal>
	);
};

OauthSuccessContent.proptypes = {
	close: PropTypes.func.isRequired,
};

export default compose( [
	withSelect( ( select ) => {
		return {
			postType: select( 'core/editor' ).getCurrentPostType(),
		};
	} ),
] )( OauthSuccessContent );
