<?php
define( 'WP_CACHE', true );

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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
// define( 'DB_NAME', '' );

/** Database username */
// define( 'DB_USER', '' );

/** Database password */
// define( 'DB_PASSWORD', '' );

/** Database hostname */
// define( 'DB_HOST', '' );

/** Database charset to use in creating database tables. */
// define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
// define( 'DB_COLLATE', '' );

define( 'DB_NAME', getenv('DB_NAME') );
define( 'DB_USER', getenv('DB_USER') );
define( 'DB_PASSWORD', getenv('DB_PASSWORD') );
define( 'DB_HOST', getenv('DB_HOST') );
define( 'DB_CHARSET', 'utf8' );
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
define( 'AUTH_KEY',          'gCZMGgQdtU__%l5h.1hqOeX3j]}enF_Q$p4aj2Z<0=F>Ui(GG=m,LT@>*y5h3T#w' );
define( 'SECURE_AUTH_KEY',   'x#zH|!Vcu? F>Fc;;n.rEU<B`O,1^zDNMjx0,~+jDe`kj@yLz^e0 nZeI>z01^.3' );
define( 'LOGGED_IN_KEY',     '][LCVK+2$>L3NGi}(*fsW6;l1qo[z{Kc-!,[NN(A7Z%~<|5v(a?*9Wp3T,/Bk6Jo' );
define( 'NONCE_KEY',         'BtNF#NcD@ZG$k~R`2++yz9+T*Fk&2+pOJ{t%6uGw57BI#9Vz7kOb+wRa]s*(fE_b' );
define( 'AUTH_SALT',         '8%yTENL.itpoxbhD#9MWj~8+Ty5oX|V7O0_(``kEX3p1T{Gmd<GO.zY&k$-LXj&&' );
define( 'SECURE_AUTH_SALT',  'K(gXD9y8zn,E{G@{QxfLpbo25<m>p8E0J=9?AUM`&)QQfYol/SN7:n1kk(~a?2``' );
define( 'LOGGED_IN_SALT',    'tf/36jG1ns;;kzO$w_.ex|T|c~lf!sXT[cRN44.lQ}U@9V:2P`;V5~ov{^aqDka|' );
define( 'NONCE_SALT',        '.6=+@XJF%gTW^!u)}Z^SK1z5*)Z9)<R[4*$TxQiZ(75!=[]&bJy42&[yXpy22HXF' );
define( 'WP_CACHE_KEY_SALT', 'i=r@B.T_A6qldJ6.zPoPVIqn8c0nmJ3_O4oxch;)@8z*Z6vhljA[Yzql)-`t6r+q' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '98e8e5ab29158e6d25df6e509496ed3f' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );

define( 'WP_ALLOW_MULTISITE', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
