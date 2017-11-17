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
define('DB_NAME', 'walthams_wp216');

/** MySQL database username */
define('DB_USER', 'walthams_wp216');

/** MySQL database password */
define('DB_PASSWORD', '9!3S!pFFw5');

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
define('AUTH_KEY',         '7ocykd4uea9dmnrpguidjkylgo1ygzhqz7hc775bsemfambse1m9wpbmbyefjwqe');
define('SECURE_AUTH_KEY',  'k6rn1gw76egsxhp5qo6bcmmqwlf7jtgpxqz9jnqdi21daup7iledtg2ixiqmq2re');
define('LOGGED_IN_KEY',    't0ffxg3oppye52ijzvzoopuzg06wv9ocv0dovrjshl1djmrhmywxioxpqxlbdzve');
define('NONCE_KEY',        'vj9hccznxcuhufi47fqokf7tdcyzf83dtpi9zrjesvvklndiiz3ia2jwoimvv2xi');
define('AUTH_SALT',        'ocazz4keizwenowr6jdz1kupnbp67qwotllc0l68906vnqmtj7ythxbyqraovk6a');
define('SECURE_AUTH_SALT', '45jepllkfypvjfrxfsk0z3yfd5athbf4za4sgxg6kwnrosveozmjtozvlh7yw9cy');
define('LOGGED_IN_SALT',   'du2ilvgl3rtlqjvytvyxivdf08zlxkok85gaeyrm8xd5q89hwc7hq1jtftficcla');
define('NONCE_SALT',       'gfokqhjbi8ltfpy7furctqxnb6a6hbjlxhbqjpzcjeekf9qjeriqwuij8xci2hvj');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp5s_';

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
