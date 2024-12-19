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
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'toor' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         'a/ii#}w~v*h+|&|ax?HS7:!`}Yz#INO?Y8Rm[oUzCKm{rhSC_SD&]NVSs;gV?];3' );
define( 'SECURE_AUTH_KEY',  'dfNqPZjM<z`qCNc& RJD$%)ym2j;b|$s@ZB4[x<Hh)K9JB[r::mB9b)r`Dqi8~co' );
define( 'LOGGED_IN_KEY',    '8Z$<D>jWax;PhhP}%v=,xw+tc&Tymqo]!mAu}oh+jLUa/$JFy6n J}Mm9<POh>PL' );
define( 'NONCE_KEY',        'OfcD@:z%zew6G+p$fpUA0T8|@]&[Dr6QCT|)/u0nK15TeI3(_g5q||!B.MB1sAI$' );
define( 'AUTH_SALT',        '(GVz=PPj0LnYai1Q;c)G*fMv %*<6*}]u4Y$4o5V4M=o(b``Up[aZo&J{lI36Ms(' );
define( 'SECURE_AUTH_SALT', 'W/C)=Aq^q,f,0zj,&pUA)QS8vu&6IfaqTp;Xo<3$yY}W-rX~:q6d~1Z$4)l&0l^6' );
define( 'LOGGED_IN_SALT',   'F,le 0^Tk6YHARL*U#iet/wQAysk)g^Uo5]s$:Iy*aq:KAy($<6wl4n_;_8%l=2^' );
define( 'NONCE_SALT',       '|@]MCI7asRuf_NXH7lhI-/_XYeE!(`&Me4RyY^kmizcrjo+m.&^[)8/(~.#nC*cU' );

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
define( 'WP_DEBUG', true );
define('WP_DEBUG_LOG', true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
