// eslint-disable-next-line no-undef
const { get } = lodash;

/**
 * Helper to get data from added JavaScript window variable.
 *
 * @param {string} prop
 * @param {any}    defaultValue
 * @return {*} The variable.
 */
export const getData = ( prop, defaultValue = {} ) =>
	get(
		window,
		`wordproofSdk.data${ prop ? `.${ prop }` : '' }`,
		defaultValue
	);
