<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dev-local.gi.by' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'mysql-8.0' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'dhx R) e2NYI&fGa05]s[diBO_P#&G<R[^iXMW&Qge`?-1OU%q*Co5p)^a 5kvnE' );
define( 'SECURE_AUTH_KEY',  '~~RaP&5#} v#HU=p}g58I_x=oEMHne0J!5VLCj6B.EwIywm%CI9c 2)F#llru?2S' );
define( 'LOGGED_IN_KEY',    'WlU[-gG1jD8O(}:QqjeWH[Y5?.[3s[Y6s(b;.~%|em`?w~V|R=K_55;e5ieC2yo=' );
define( 'NONCE_KEY',        'CVddaNAZ9Yl3xvde^sRxbI]O8;1uJRc</#6<VuR$S &?0PHUi0M&VABsOr7J%HKH' );
define( 'AUTH_SALT',        'u`@c.?-a]]zk)<q>T{iAqt]*5lCP,DTbAZzLaP*,^daIz]s@R(M`!XaOJ9e0&:/!' );
define( 'SECURE_AUTH_SALT', 'Ym~Y38&9tM4t|!BXD 705UXk{*ZvuLfcBs~#afp*hu_8S:#02VU>O{KH^G|ZJCw-' );
define( 'LOGGED_IN_SALT',   '/^e{[,ZkSTftc8!^lUM&tD`v@X.<We^gf05V,xuAv$#2EyWkrt[%oE#H|/LXOQH<' );
define( 'NONCE_SALT',       ',u[a( 79|]9of>_12xbc3D+n<U.7~3B~fQ!#!n62[E&5U|`EGFE<S|&A]tPHUj!6' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
define('WP_HOME', 'https://dev-local.gi.by');
define('WP_SITEURL', 'https://dev-local.gi.by');

// Set timezone to Europe/Minsk (Belarus)
// date_default_timezone_set('Europe/Minsk');