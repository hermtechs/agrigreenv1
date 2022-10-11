import apiFetch from '@wordpress/api-fetch';
import { getData } from './helpers/data';

const { createReduxStore, registerStore, register } = wp.data;

const storeName = 'wordproof';
const initialState = {
	isAuthenticated: getData( 'is_authenticated', false ),
	balance: getData( 'balance', 0 ),
	selectedPostTypes: getData( 'settings.selected_post_types', [] ),
};

const actions = {
	setIsAuthenticated( isAuthenticated ) {
		return {
			type: 'SET_IS_AUTHENTICATED',
			isAuthenticated,
		};
	},
	getIsAuthenticated() {
		return {
			type: 'GET_IS_AUTHENTICATED',
		};
	},
	setBalance( balance ) {
		return {
			type: 'SET_BALANCE',
			balance,
		};
	},
	getBalance() {
		return {
			type: 'GET_BALANCE',
		};
	},
	setSelectedPostTypes( selectedPostTypes ) {
		return {
			type: 'SET_SELECTED_POST_TYPES',
			selectedPostTypes,
		};
	},
	getSelectedPostTypes() {
		return {
			type: 'GET_SELECTED_POST_TYPES',
		};
	},
};

const reducer = ( state = initialState, action ) => {
	switch ( action.type ) {
		case 'SET_IS_AUTHENTICATED': {
			return {
				...state,
				isAuthenticated: action.isAuthenticated,
			};
		}
		case 'SET_BALANCE': {
			return {
				...state,
				balance: action.balance,
			};
		}
		case 'SET_SELECTED_POST_TYPES': {
			return {
				...state,
				selectedPostTypes: action.selectedPostTypes,
			};
		}
		default: {
			return state;
		}
	}
};

const selectors = {
	getIsAuthenticated( state ) {
		const { isAuthenticated } = state;
		return isAuthenticated;
	},
	getBalance( state ) {
		const { balance } = state;
		return balance;
	},
	getSelectedPostTypes( state ) {
		const { selectedPostTypes } = state;
		return selectedPostTypes;
	},
};

const controls = {
	fetchIsAuthenticated( action ) {
		return apiFetch( { path: action.path } );
	},
};
const resolvers = {
	*fetchIsAuthenticated() {
		const isAuthenticated = yield actions.getIsAuthenticated();
		return actions.setIsAuthenticated( isAuthenticated );
	},
};

const storeConfig = {
	reducer,
	controls,
	selectors,
	resolvers,
	actions,
};

if ( createReduxStore ) {
	const store = createReduxStore( storeName, storeConfig );
	register( store );
} else {
	/*
	 * Compatibility fix for WP 5.6.
	 * Remove this and the related import when WP 5.6 is no longer supported.
	 */
	registerStore( storeName, storeConfig );
}
