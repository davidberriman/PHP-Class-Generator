<?php
// DIRECTORY_SEPARATOR is a PHP pre-defined constant
// (\ for Windows, / for Unix)
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

/*********** Need to change site root!! **************/
// on a mac it would be something like:
defined('SITE_ROOT') ? null : define('SITE_ROOT', DS.'Users'.DS.'davidberriman'.DS.'Sites'.DS.'classGenerator');
// on a server it might be something like:
// defined('SITE_ROOT') ? null : define('SITE_ROOT', DS.'home'.DS.'my_acccont_name'.DS);

defined('LIB_PATH') ? null : define('LIB_PATH', SITE_ROOT.DS.'include');

// Database Constants
defined('DB_SERVER') ? null : define("DB_SERVER", "localhost");
defined('DB_USER')   ? null : define("DB_USER", "db_classGenerator_usr");
defined('DB_PASS')   ? null : define("DB_PASS", "db_classGenerator_pass");
defined('DB_NAME')   ? null : define("DB_NAME", "db_classGenerator");

// make sure everything is set to UTF-8 (we don't want mixed encoding!)
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
date_default_timezone_set('Europe/London');

defined('ADMIN_EMAIL') ? null : define("ADMIN_EMAIL", "admin@adminEmail.com");
defined('WEBSITE_EMAIL') ? null : define("WEBSITE_EMAIL", "admin@myWebsite.com");

// set this to "file" / "email" / "db" to save the error logs to a file / email / database respectively
defined('ERROR_LOG_TYPE')   ? null : define("ERROR_LOG_TYPE", "file");

// directory for error logs when using file type
defined('ERROR_LOG_DIR')   ? null : define("ERROR_LOG_DIR", LIB_PATH.DS."logs/php_error.log");

// load the database and Sanitize classes for each of the table classes to use
if(!isSet($database)){require_once(LIB_PATH.DS."database.php");};
if(!class_exists('Sanitize')){require_once(LIB_PATH.DS."sanitize.php");};
if(!class_exists('ErrorLog')){require_once(LIB_PATH.DS."class.error_log.php");};
?>