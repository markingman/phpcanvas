<?php

$config = [];

/**
 * Default app root directory
 *
 * Presumes we're in [root]/vendors/devname/projname/src/config/ or [root]/src/config/
 */
$config['DIR_ROOT'] = strpos(__DIR__, '/vendor/') !== false ?
	 realpath(__DIR__ . '/../../../../../') : realpath(__DIR__ . '/../../');
/**
 * Application name (for internal use)
 *
 * Example use is being emailed to admin when an error occurs. Not intended for
 * display to regular users.
 */
$config['APP_NAME'] = 'Sample App';

/**
 * The site operates differently depending on it's mode
 */
$config['SITE_MODE'] = 'prod';

/**
 * Domain name or IP address of the site
 *
 * No http:// or trailing slash
 */
$config['SITE_DOMAIN'] = 'localhost';

/**
 * URL path of the site from domain
 *
 * Should not end with '/', e.g. '/dev/site/path'
 */
$config['SITE_PATH'] = '';

/**
 * URI of the site] = but no protocol - include all sub directories
 */
$config['SITE_URL'] = $config['SITE_DOMAIN'] . $config['SITE_PATH'] . '/';

/**
 * Email address of the site's system administrator
 *
 * Gets set error emails etcetera.
 */
$config['MAIL_ADMIN'] = 'root';

/**
 * Email address of the default mail sender
 */
$config['MAIL_SENDER'] = 'www';

/**
 * Email on/off control (can work independently of {@link SITE_MODE})
 */
$config['MAIL_ON'] = ($config['SITE_MODE'] == 'prod') ? true : false;

/**
 * Default locale
 */
$config['LOCALE'] = function_exists('locale_get_default') ? locale_get_default() : 'en-GB';

/**
 * Default locale options
 */
$config['LOCALE_OPTIONS'] = $config['LOCALE'];//space dellimited list

/**
 * Controller action auto prefix (added to all URL actions)
 */
$config['ACTION_PREFIX'] = '';

/**
 * Controller action auto suffix (added to all URL actions)
 */
$config['ACTION_SUFFIX'] = '_action';

/**
 * Set common action (called before any specifc action)
 */
$config['ACTION_DEFAULT'] = 'default';

/**
 * Universal time Coordinate description (country and town name)
 *
 * See http://www.php.net/manual/en/timezones.php
 */
$config['TIMEZONE'] = /*function_exists get else : */ 'Europe/London';

/**
 * File type used by file writers to control line breaks
 */
$config['LE'] = PHP_EOL;

/**
 * Directory separator
 *
 * Note: can use '/' in all strings, this for things like OS provided paths
 */
$config['DS'] = DIRECTORY_SEPARATOR;

/**
 * Table prefix
 *
 * Used with database tables.
 */
// $config['TP'] = '';

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
 * Filepath to the application's sessions directory
 *
 * Used if using file based sessions
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
 * Minimum PHP version
 *
 * Set a minium PHP version requirement
 */
// $config['CONFIG_MIN_PHP'] = '7';

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
 * General file permission
 *
 * File copying/creating functions should use at least this permission when
 * writing files (if nothing else is specified).
 */
$config['PRM_FILE_COPY'] = 0644;

/**
 * General directory permission
 *
 * Directory copying/creating functions should use at least this permission when
 * writing directories (if nothing else is specified).
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
 * Name of variable for URL variable prefix 'arg'[n]
 *
 * Note values start with '1' (arg1) not '0'
 */
$config['URL_ARG'] = 'arg';

/**
 * Generic setting for 'use cache' or not
 */
$config['CACHE_USE'] = ($config['SITE_MODE'] === 'prod');

/**
 * Default cache time
 */
$config['CACHE_TIME'] = 900;

/**
 * Regular expression to scan for routes in short paths
 */
$config['ROUTES_GLOB'] = 'config/routes.php';

/**
 * Default finder paths
 */
$config['DIRS_FINDER'] = [
	'app/app' => $config['DIR_ROOT'] . '/app',
	'phpcanvas/phpcanvas' => realpath(__DIR__ . '/..'),
];

$config['DIRS_VIEW'] = [
	'app/app' => $config['DIR_ROOT'] . '/app/views',
	'phpcanvas/phpcanvas' => realpath(__DIR__ . '/../views'),
];

return $config;