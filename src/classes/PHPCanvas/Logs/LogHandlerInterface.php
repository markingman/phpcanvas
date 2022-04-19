<?php

namespace PHPCanvas\Logs;

interface LogHandlerInterface
{
	public function emerg(string $log);

	public function alert(string $log);

	public function crit(string $log);

	public function err(string $log);

	public function waring(string $log);

	public function notice(string $log);

	public function debug(string $log);

	public function log(string $log, int $type = 0);
}
