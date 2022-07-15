<?php

namespace PHPCanvas\Errors;

use Throwable;

class ErrorHandler
{
	protected $view;
	protected $log;

	public function set_view(callable $view): void
	{
		$this->view = $view;
	}

	public function set_log(callable $log): void
	{
		$this->log = $log;
	}

	public function handle_error($num, $str, $file, $line, $context = null): void
	{
		if ($num === 0 or is_null($num)) {
			return;
		}

		$this->handle_exception(
			new \ErrorException($str, 0, $num, $file, $line)
		);
	}

	public function handle_exception(Throwable $e): void
	{
		if ($this->ignore($e)) {
			return;
		}

		if ($this->log) {
			call_user_func($this->log, $e);
		}

		if ($this->view) {
			call_user_func($this->view, $e);
		}

		exit;
	}

	public function handle_fatal(): void
	{
		if ($error = error_get_last()) {
			$this->handle_error($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}

	public function handle_shutdown(): void
	{
		if (($error = error_get_last()) !== null) {
			if (in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
				$this->handle_fatal();
			}
		}
	}

	protected function ignore(Throwable $e = null): bool
	{
		switch (error_reporting()) {
			case null:
			case 0:
			case E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE: // 4437
				return true;
			default:
				return false;
		}
	}
}
