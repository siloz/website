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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'deertrot');

/** MySQL hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         'h]g87=o.f`61rrDQqzaLEV<Jkjy},SJ$+9+<s^;6dD5EQW2f-b|qDDE}cdmXe_7E');
define('SECURE_AUTH_KEY',  'lT4fvM@% %9:LL0{oWR{&O||M$OC:A=a*fMa:u,x7?Mq!;xEu+&Ex,F{Z|c`?B-,');
define('LOGGED_IN_KEY',    'm&JgF-QxJeOSGe{qW:Bzv# K5*kM`D?P=D)~}.$F+BJ8E~Vy`i$`1z}GU#,|GSXS');
define('NONCE_KEY',        '_uKMU]~|Y;/HK?4Vi-G{-|7A:%| v&nkh_-LH|)%)*e+85hY+TdmCDkvCRaIO`*f');
define('AUTH_SALT',        'i!qcR|Y&[P60T%t,wlE8CeN_.BmixaQ}b|FAK yPJ+ek!jsdfR6FoZ^f1JEN5(uK');
define('SECURE_AUTH_SALT', '1ukW)BH-]sqvHN6S;,CVfMHaGPhiGj]sJv};`e~S9C~;|F-j81P.Xg)?7h2I$|?H');
define('LOGGED_IN_SALT',   '!T 0M#%V7[(Q&gHV,pIQ)Sl4(YlPqBnrOg6.a%pbR=G8a-cXK c|ufbhTb,0w_I:');
define('NONCE_SALT',       'x? #y#@Yn-B>8F{I-602x/uNr>+w$04$OQdaV^@@+mnND-!!b9nj m~2as2i]Pdi');

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
