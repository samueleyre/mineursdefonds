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
define( 'DB_NAME', 'wordpress');

/** MySQL database username */
define( 'DB_USER', 'wordpress');

/** MySQL database password */
define( 'DB_PASSWORD', 'wordpress');

/** MySQL hostname */
define( 'DB_HOST', 'db:3306');

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '"3eEV 6,j2Gt86gG0W{HEfb )=!.Tu>fo#4wG6Q0?(00&;8QS6?2IBBPCzr(u|;-M\')";');
define( 'SECURE_AUTH_KEY',  '"Vw35(?cz@6-@< XU|pcS4,1)||fYAufa6!KI1!bl43O:U0.ey!qi,df*u+Kk!G~d\')";');
define( 'LOGGED_IN_KEY',    '"FuZ|Ev3>,M|z?328eO$fzYSh&& _:RQAI&opLNCFJ3obLCm7S$0&yX-Sg1|^X.q(\')";');
define( 'NONCE_KEY',        '"JsJ#aE8U^>f}>7FzZS!k+;9HT&t[!82,z)uQrBJ_.-h6?u8/I[@ZB@+dUl+;tC?\')";');
define( 'AUTH_SALT',        '"6KfRS ehUw#k{cE<y:/, -~hui({$5kL) L,(.dDj.FaONXPa<KiA9Omj9VAJ-=<\')";');
define( 'SECURE_AUTH_SALT', '"eO>; ZT1KuX*5]=.mN-L!<t_abtMA-@|3mYz{n_J^55HT&lb1@7laPv5q5f<qt5<\')";');
define( 'LOGGED_IN_SALT',   '"f}tx5l&WXBs7JV{Jp[.vll]oZ>6}.=?JZ`+ ;,#+q]yT!y)ZA#HJ!4bJmF{Y+cyi\')";');
define( 'NONCE_SALT',       '"i_{Lgr>(UA@l!|J.$`rIq-PTo@^0Y 1n=+v3.6X+#(t6}+F*]5?s_+M-]4z<,+/p\')";');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.-
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
define( 'WP_DEBUG', getenv('WORDPRESS_DEBUG_MODE') );

// If we're behind a proxy server and using HTTPS, we need to alert Wordpress of that fact
// see also http://codex.wordpress.org/Administration_Over_SSL#Using_a_Reverse_Proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
	$_SERVER['HTTPS'] = 'on';
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
