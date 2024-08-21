<?php

namespace PHPCanvas\Errors;

use Throwable;
use ErrorException;
use Closure;

/*
Example:

1) Set handlers:

register_shutdown_function([$App->Container['Errors'], 'handle_shutdown']);
set_error_handler([$App->Container['Errors'], 'handle_error']);
set_exception_handler([$App->Container['Errors'], 'handle_exception']);

2) Set log and view:

$App->Container['Errors']->set_log(function (Throwable $e) use ($App) {
	// log this string
	return (include $App->Config->DIR_FRAMEWORK . '/functions/error_log.php')($e, $App->Container['Log'], $App->is_prod());
});

$App->Container['Errors']->set_view(function (Throwable $e, $m = null) use ($App) {
	// if view
	return (include $App->Config->DIR_APP . '/functions/error_view_html.php')($App->Container['Response'], $App->Container['Page'], $e);
});
*/

class ErrorHandler
{
	protected bool $terminate = true;
	protected ?Closure $view = null;
	protected ?Closure $log = null;

	public function set_terminate(bool $exit)
	{
		$this->terminate = $exit;
	}	

	public function set_view(Closure $view): void
	{
		$this->view = $view;
	}

	public function set_log(Closure $log): void
	{
		$this->log = $log;
	}
/*
// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Determine the severity of the error
    switch ($errno) {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
            // Log the error and exit
            error_log("Fatal Error [$errno]: $errstr in $errfile on line $errline");
            exit(1);
            break;

        case E_WARNING:
        case E_USER_WARNING:
            // Log the warning and continue
            error_log("Warning [$errno]: $errstr in $errfile on line $errline");
            break;

        case E_NOTICE:
        case E_USER_NOTICE:
            // Log the notice and continue
            error_log("Notice [$errno]: $errstr in $errfile on line $errline");
            break;

        default:
            // Handle unknown error types
            error_log("Unknown error type: [$errno]: $errstr in $errfile on line $errline");
            break;
    }

    // Don't execute PHP's internal error handler
    return true;
}

// Custom exception handler

// Set custom error and exception handlers
set_error_handler("customErrorHandler");
set_exception_handler("customExceptionHandler");

// Example custom exception class for critical exceptions
class CriticalException extends Exception {}

// Example usage
try {
    // Some code that might throw exceptions
    throw new CriticalException("Critical failure");
} catch (Exception $e) {
    customExceptionHandler($e);
}

*/
	public function handle_error(int $errno,  string $errstr,  ?string $errfile = null,  ?int $errline = null): bool
	{
		if ($errno < 1) {//TODO when is this case
			return false;
		}

		if (error_reporting() & $e->getCode()) {// if code is at reporting level
			return true;
		}

// 		if (error_reporting() & $e->getCode()) {// if code is at reporting level
// 			return false;
// 		}

// CriticalException or ErrorException
		$this->handle_exception(// change error messages into ErrorException
			//not this is not `throw new ...`
			new ErrorException($errstr, 0, $errno, $errfile, $errline)
		);

		return true;
	}

/*
function customExceptionHandler($exception) {
    // Log the exception
    error_log("Uncaught Exception: " . $exception->getMessage());

    // Determine if the exception is fatal
    if ($exception instanceof CriticalException) {
        exit(1);
    } else {
        // Optionally, display a user-friendly error message
        echo "An error occurred, please try again later.";
    }
}

*/
	public function handle_exception(Throwable $e): void
	{
		if ($this->log) {// callback can ignore or log
			call_user_func($this->log, $e);
		}

		$exit = 0;
		if ($this->view) {// callback can ignore or view
			$exit = call_user_func($this->view, $e);
			// TODO: view can set exit
		}

		if ($this->terminate) {// omit exit for testing
			exit($e->getCode());
		}
	}

	public function handle_shutdown(): void
	{
		if (($err = error_get_last()) !== null) {
			if ((E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR) & $err['type']) {
				// fatal error handler
				$this->handle_error($err['type'], $err['message'], $err['file'], $err['line']);
			}
		}
	}

	public static function log(Throwable $e, ?LogHandler $LogHandler = null): string
	{
		// basic placeholder, override with custom function

		$sev = is_callable([$e, 'getSeverity']) ? $e->getSeverity() : error_get_last()['type'];
		$log = sprintf(
			"%s\t%s\t%s\t%s",
			$sev, $e->getCode(), $e->getMessage(), $e->getFile() . ':' . $e->getLine()
		);
		$lev = match($sev) {
			E_ERROR => LOG_ERR,
			E_WARNING => LOG_WARNING,
			E_PARSE => LOG_ALERT,
			E_NOTICE => LOG_NOTICE,
			E_CORE_ERROR => LOG_ERR,
			E_CORE_WARNING => LOG_WARNING,
			E_COMPILE_ERROR => LOG_ERR,
			E_COMPILE_WARNING => LOG_ALERT,
			E_USER_ERROR => LOG_ERR,
			E_USER_WARNING => LOG_WARNING,
			E_USER_NOTICE => LOG_NOTICE,
			E_STRICT => LOG_NOTICE,
			E_RECOVERABLE_ERROR => LOG_ERR,
			E_DEPRECATED => LOG_NOTICE,
			E_USER_DEPRECATED => LOG_NOTICE,
			E_ALL => LOG_NOTICE,
			default => LOG_CRIT,
		};
		if ($LogHandler) {
			$LogHandler->log($log, $lev);
		} else {
			error_log(date('c') . "\t" . $log);
		}
	}

	public static function view(Throwable $e, $m = null): string
	{
		// basic placeholder, override with custom function

		if (PHP_SAPI === 'cli') {
			return vsprintf(
				PHP_EOL .
				"\033[3;101m  %1\$s  \033[0m" . PHP_EOL .
				'%2$s' . PHP_EOL .
				'%3$s:%4$s' . PHP_EOL .
				PHP_EOL,
				[
					1 => get_class($e),
					2 => $e->getMessage(),
					3 => $e->getFile(),
					4 => $e->getLine(),
				]
			);
		} else { 
			$html = <<<__
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>%1\$s</title>
	<style>*{line-height:1.6}</style>
</head>
<body>
	<pre><b>%1\$s</b>
%2\$s
%3\$s:%4\$s</pre>
</body>
</html>
__;
			return vsprintf(
				$html,
				[
					1 => get_class($e),
					2 => $e->getMessage(),
					3 => $e->getFile(),
					4 => $e->getLine(),
				]
			);
		}
	}
}
