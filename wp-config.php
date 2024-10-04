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
define( 'DB_NAME', 'wordpress_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'L}e$1Vr*uZ:jffgQR|NlJ?c~5udOzZaS{Qg8^saT&Ozr%jkGUN31t}l/sz;9$XjY' );
define( 'SECURE_AUTH_KEY',  '|Y_ e_f5&~J!>9d5%6NEBSRw8;i1]i.9>91IYQi#V4O[ib_YdM+`)ug5|D_}W^sO' );
define( 'LOGGED_IN_KEY',    'yP!c?KAZP`2 dM>Sxza#I2VtB0+Nf uFu;]Skw>yYSR>`N=+)}fB5aAY=l]Spg6%' );
define( 'NONCE_KEY',        'R4EV/2bgzz&>$RN+p#8h:u@`rY`9-:Ettu+}hDY9P:W-/1JGkpv*OF-U.)Vh:tWq' );
define( 'AUTH_SALT',        '3OJFz.:w_m92^yRqC< vb3}lad1mRjW=bn.!Kphq>`t9bef ;o[MS=-1:v+U~0lC' );
define( 'SECURE_AUTH_SALT', 'K(UDp5IEw``Oyb@= UuX0(5$A?=_E(20h*A+Q+K)Aua?6]Jrn{#QiT)1kJP%YaC4' );
define( 'LOGGED_IN_SALT',   'BnWssJZ|8(}ytz2j4Oi7ryJKgBt?fnP**tPbQ}D-o2-P6?~,i7-t9w%(~@)`I}j7' );
define( 'NONCE_SALT',       'n<|D!3J/4YE. PCMBhf,]X-0Z@!AK4Nkd{ZHE&7{W.yhTn&alJDG(K&>^U5Y[XWa' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true );

/* Add any custom values between this line and the "stop editing" line. */


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

