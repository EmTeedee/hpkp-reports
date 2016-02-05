<?php
/**
 * Initialize the system for use.
 *
 * Load commonly used libraries and initialize constants
 */

/*
 * Set some important path constants
 */
$path = dirname(__FILE__);
define('CLIENT_PATH', $path . '/../');
unset($path);

/*
 * Parse the INI file, which will contain general parameters.
 */
$conf = CLIENT_PATH . 'includes/config.ini';
if (!is_readable($conf)) {
    print('FATAL: There is no configuration file.');
    exit();
}
$ini = parse_ini_file($conf, true);
unset($conf);

// system libraries
include 'Mail.php';
include 'Mail/mime.php';

// our own libraries
require_once CLIENT_PATH . 'includes/db.php';
require_once CLIENT_PATH . 'includes/utils.php';

// define stuff
define('DEBUG', $ini['debug']);

// database constants
define('DB_HOSTNAME', $ini['DB']['hostname']);
define('DB_DATABASE', $ini['DB']['database']);
define('DB_USERNAME', $ini['DB']['username']);
define('DB_PASSWORD', $ini['DB']['password']);

// Tables
define('TABLE_REPORT', 'reports');
define('TABLE_EMAIL', 'email_alerts');

// Alerts
define('ALERTS_ENABLED', $ini['Alerts']['enabled']);
define('ALERTS_EMAIL', $ini['Alerts']['email']);
define('ALERTS_THROTTLE_HOURS', $ini['Alerts']['throttle_hours']);
define('ALERTS_THROTTLE_BY_DOMAIN', $ini['Alerts']['throttle_by_domain']);

/**
 * Get a Database instance
 * 
 */
function db() {
    static $_db = null;
    if (is_null($_db)) {
        $_db = new DB(DB_HOSTNAME, DB_DATABASE, DB_USERNAME, DB_PASSWORD);
    }
    return $_db;
}