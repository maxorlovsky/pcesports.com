<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'pentaclick_wp');

/** MySQL database username */
define('DB_USER', 'pcusertest_wp');

/** MySQL database password */
define('DB_PASSWORD', 'vBO87Jo7IpXlr3gO');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('WP_CONTENT_DIR', dirname(__FILE__) . '/wp-content' );
define('WP_CONTENT_URL', $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/wp-content' );

//define('WP_PLUGIN_DIR', dirname(__FILE__) . '/wp-plugins' );
//define('WP_PLUGIN_URL', $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/wp-plugins' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'a9H11_Hvg3|&i8b@(OLVa&%0!y>*`&K[:<D2zt-6;Kc7|xlN0G;1P0R={l1</v}|');
define('SECURE_AUTH_KEY',  '=A<JR`:KF4`/$|n#cq(:gZ8Luu,.+Ho7.I|kh@[!T7YCQS($u.*yWkf}{:E+:.Fu');
define('LOGGED_IN_KEY',    '7?g~1L;:fDW/)i8]Y0/>mNHR{JB?|v?xJkq9kJk@tbI/)eOB|u9]@^@Jg4ZRWE|I');
define('NONCE_KEY',        'RXS9q}b,)R+Dz9R3rZy2tr/F;V*(19<h4@sz@#|eGy-F`X%S{=SIB?xfMos_4q4S');
define('AUTH_SALT',        'bWzo$qz$C.nV%/9.HGGb!+%kK6zX6{<90feV2I2s+s&@8ZV=e&/9j?e}MXCO^ana');
define('SECURE_AUTH_SALT', 'e.F0q%Q+8{+-NX$=Q;`s++nZb_zFOL%k.F:Q7rv(fvpu8o|&s_z]z;~`7/iiyH?;');
define('LOGGED_IN_SALT',   '-WdOXw`*m3z>M<X.4(pFwve,]?<:8tvZ@+q612C}R1F)$3Op={Is%hx~7^y !Qem');
define('NONCE_SALT',       'OQ?z3=S#yOF2}CR3@O!J{Zhs^@FXWEJxG3F|35:omMU5owdDBP(Xbg}FpU*!^ B<');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
