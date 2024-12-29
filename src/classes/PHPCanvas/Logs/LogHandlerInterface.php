<?php

namespace PHPCanvas\Logs;

interface LogHandlerInterface
{
	public function emerg(string $log): void;

	public function alert(string $log): void;

	public function crit(string $log): void;

	public function err(string $log): void;

	public function waring(string $log): void;

	public function notice(string $log): void;

	public function debug(string $log): void;

	public function log(string $log, int $type = 0): void;
}
