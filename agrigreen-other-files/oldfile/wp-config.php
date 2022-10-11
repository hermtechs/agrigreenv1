<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'agrigree_WP878');

/** Database username */
define('DB_USER', 'agrigree_WP878');

/** Database password */
define('DB_PASSWORD', 'U^aM6]}/DCtzcV:Xa');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', '8f1f8a9f2d28974134aba0f7d52e688a267fd6d16b3288b80e4a4bcfe181b4ab');
define('SECURE_AUTH_KEY', '66011cf947f9d89d80bc8779b83088d65d4012a415e1a3c47dbb74a1abef995b');
define('LOGGED_IN_KEY', 'c79b4a14b6a4d849025d4e5866bc281033ef230447fb317da61d92d552b4bd33');
define('NONCE_KEY', '339447806029ef14a578cb47b5fea71e92ba8671abc6c8bcf2a6a0a0298a1b02');
define('AUTH_SALT', 'ce875323bc7d0afeb4ca6cf596bab22d1885b87a001dbdda045aea6299204678');
define('SECURE_AUTH_SALT', 'dc455b428c60b05edcc303ffd2281f09375661b40661045e7776c6a8e910672c');
define('LOGGED_IN_SALT', '456d252acb3bf30fc4cf05521817679a046a55686c4d354a05fc7f8e3ca3403a');
define('NONCE_SALT', '2fbbd563cd055b9551488d609b16bb934698912dad00e61aff791c79d548149c');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'ALd_';
define('WP_CRON_LOCK_TIMEOUT', 120);
define('AUTOSAVE_INTERVAL', 300);
define('WP_POST_REVISIONS', 5);
define('EMPTY_TRASH_DAYS', 7);
define('WP_AUTO_UPDATE_CORE', true);

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
