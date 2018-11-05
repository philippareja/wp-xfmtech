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
define('DB_NAME', 'xfm_tech');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         's|W,>`a,d2l:~4`w`{tc2,sCA[k`^x-6OrlMj_PzDnzTiF^dZHfd&Rvy853KJAi1');
define('SECURE_AUTH_KEY',  'JNxW^X|7vZq<hq=g-&}]cN .pp]FY]+W<Bzx#;d#<CEx+ROk#F-sSNM|0-yfwD/C');
define('LOGGED_IN_KEY',    'KJ:6TVH@76*L)(}vo-N7|<:xzSj-N@m2`~)qs)h<!k?uI2h/!Y5SP9Zka [&WpCt');
define('NONCE_KEY',        '#HI%Emcnjk_,2l6JK{bs}$rp_S]fd*w=l/o2MaOt*#nR#L4_f{C-~9Ht*(6x+:}Z');
define('AUTH_SALT',        'O)>542CiDG7_>/syw[BLO3#f<sxZ8^~soNpuCI6WhW{HYo9?&ImNba]t0zl?mJ]i');
define('SECURE_AUTH_SALT', '5Rm?c>`pB2T,$$QXu:a/4rBef2H}nvy7b!tlz.Bv>.@Faf[#U-K/f.a$Oc= L{de');
define('LOGGED_IN_SALT',   '3JC53aOf+YjOYE=Bz/FDtrqt{N-bq]RQRl&f}z!D<_?Qy<<,W-,(67UJJ/)ql>{B');
define('NONCE_SALT',       '+9X4),OfVv]+L,|3eB8lTr)=;MD~oE!>vi -VaB0YJC)ez![4!D@Q_&hhWHx@lQG');

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
