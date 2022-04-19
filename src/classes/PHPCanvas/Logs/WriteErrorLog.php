<?php

namespace PHPCanvas\Logs;

class WriteErrorLog implements LogWriteInterface
{
	private $path;

	public function __construct(string $path)
	{
		$this->path = $path;
	}

	public function write(string $log, string $type = '')
	{
		error_log($log . PHP_EOL, 3, $this->path . '/' . basename($type ?: 'error') . '.log');
	}
}
