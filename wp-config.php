<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */
 
$breakdown = explode('.', $_SERVER['HTTP_HOST']);

if ($breakdown[0] == 'dev') {
    define('DB_USER', 'pcuserdev');
    define('DB_PASSWORD', 'd9829*d@)09aSJD22@');
    define('DB_NAME', 'pentaclick_dev');
    define('DB_HOST', 'pcesports.com');
    define('ENV', 'dev');
}
else {
    define('DB_USER', 'pcuserdev');
    define('DB_PASSWORD', 'd9829*d@)09aSJD22@');
    define('DB_NAME', 'pentaclick_dev');
    define('DB_HOST', '127.0.0.1');
    define('ENV', 'test');
}

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'HkZG+$-wxOvN$N 0&UE/&xSA2W.|wQm-Q9:53`R{S}.EZr;%5m,_gnH<RJcs!C_r');
define('SECURE_AUTH_KEY',  '#E)f(O+!%w>A5V)-%*q`o5Yhug:b|M5aD,{=;[z?hi7=LGKja>vZ6H1/TNLck}Ev');
define('LOGGED_IN_KEY',    'loT3 ;DnXV8Gw(6qpG/K-dBF[9~-X0?eZm$0@LD#&9g`5-7}tR0>nbsF:jl,+U/7');
define('NONCE_KEY',        '5K_/r$j6kU&)xR=e=_r<EGmq];V}$*#We,L+/[5T$mRrx`Z[Zg7-7&o9PY)7E-J!');
define('AUTH_SALT',        '{LfOMf;L4?[?]+:B8#^ya-V~s_v9rQ%mWx/Mf:#Z)o96r(;0)4r1c MQQ=_}4t|a');
define('SECURE_AUTH_SALT', 'tSSha$d>UhUset=t.XIrkss,S]7h -RQc&:%ll<|yH<Rf4;J+]?(>;JL/UDht&w$');
define('LOGGED_IN_SALT',   '%fyq:)YM9!<c-AOamGa%-a:j4MNpu& yi+|4TpkAFmQ_@}UakV&v@}`%2t]k<`eZ');
define('NONCE_SALT',       '#(RZ|gd-lte/Mac,+v* :oK_R.#B#W^lRnU<{ZpDn0|XN>{vY?FY|6?8rQJ}_wS:');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
