<?php

$config = [];

/**
 *  Default app root directory (presumes [root]/vendors/name/project/src/config/ or [root]/src/config/)
 */
$config['DIR_ROOT'] = str_contains(__DIR__, '/vendor/') ?
	 realpath(__DIR__ . '/../../../../../') : realpath(__DIR__ . '/../../');

/**
 * Application name (for internal use, not intended for public display)
 */
$config['APP_NAME'] = 'Sample App';

/**
 * The site operates differently depending on it's mode
 */
$config['ENV'] = 'prod';

/**
 * Domain name or IP address of the site (no http:// or trailing slash)
 */
$config['SITE_DOMAIN'] = 'localhost';

/**
 * URL path of the site from domain (no trailing slash, e.g. "" or "/path")
 */
$config['SITE_PATH'] = '';

/**
 * URI of the site (no protocol, include all sub paths)
 */
$config['SITE_URL'] = $config['SITE_DOMAIN'] . $config['SITE_PATH'] . '/';

/**
 * Controller action auto prefix (added to all URL actions)
 */
$config['ACTION_PREFIX'] = '';

/**
 * Controller action auto suffix (added to all URL actions)
 */
$config['ACTION_SUFFIX'] = '_action';

/**
 * Set common action (called before any specific action)
 */
$config['ACTION_DEFAULT'] = 'default';

/**
 * Universal Time Coordinate description (country and town name)
 */
$config['TIMEZONE'] = date_default_timezone_get();

/**
 * File type used by file writers to control line breaks
 */
//$config['LE'] = PHP_EOL;

/**
 * Directory separator
 */
//$config['DS'] = DIRECTORY_SEPARATOR;

/**
 * Filepath to the private filesystem
 */
$config['DIR_APP'] = $config['DIR_ROOT'] . '/app';

/**
 * Filepath to the DIR_CACHE directory
 */
$config['DIR_CACHE'] = $config['DIR_APP'] . '/cache';

/**
 * Filepath to the application config directory
 */
$config['DIR_CONFIG'] = $config['DIR_APP'] . '/config';

/**
 * Filepath to the DIR_DATA directory
 */
$config['DIR_DATA'] = $config['DIR_APP'] . '/data';

/**
 * Filepath to the framework filesystem
 */
$config['DIR_FRAMEWORK'] = $config['DIR_ROOT'] . '/phpcanvas';

/**
 * Filepath to the library directory
 */
$config['DIR_LIB'] = $config['DIR_APP'] . '/lib';

/**
 * Filepath to the DIR_LOCALE directory
 */
$config['DIR_LOCALE'] = $config['DIR_APP'] . '/locale';

/**
 * Filepath to the DIR_LOGS directory
 */
$config['DIR_LOGS'] = $config['DIR_APP'] . '/logs';

/**
 * Filepath to the public filesystem
 */
$config['DIR_SITE'] = $config['DIR_ROOT'] . '/httpdocs';

/**
 * Filepath to the application's sessions directory (if using file based sessions)
 */
$config['DIR_SESS'] = $config['DIR_APP'] . '/sessions';

/**
 * Filepath to the application's default Temp Directory
 */
$config['DIR_TEMP'] = $config['DIR_APP'] . '/tmp';

/**
 * Filepath to the DIR_VIEWS directory
 */
$config['DIR_VIEWS'] = $config['DIR_APP'] . '/views';

/**
 * Developer debug
 */
$config['DEBUG'] = false;

/**
 * Errors report to logs
 */
$config['ERROR_LOG'] = $config['DIR_LOGS'] . '/errors.log';

/**
 * Errors report to logs
 */
$config['ERROR_LOG_LEVEL'] = E_ALL;

/**
 * Errors triggering display (blocking processing, showing error message)
 */
$config['ERROR_DISPLAY_LEVEL'] = E_ALL & ~E_NOTICE;

/**
 * Errors triggering admin email
 */
$config['ERROR_ALERT_LEVEL'] = E_CORE_ERROR | E_COMPILE_ERROR;

/**
 * General file permission (file writes should use at least this permission)
 */
$config['PRM_FILE_COPY'] = 0644;

/**
 * General directory permission (dir writes should use at least this permission)
 */
$config['PRM_DIR_COPY'] = 0755;

/**
 * Default character encoding
 */
$config['CHAR_TYPE'] = 'UTF-8';

/**
 * Default character encoding in database
 */
$config['CHAR_TYPE_DB'] = 'utf8';

/**
 * Name of variable for URL variable prefix (note values start with 1 not 0, e.g: arg1, arg2)
 */
$config['URL_ARG'] = 'arg';

/**
 * Regular expression to scan for routes in short paths
 */

$config['ROUTES'] = $config['DIR_APP'] . '/config/routes.php';

return $config;
