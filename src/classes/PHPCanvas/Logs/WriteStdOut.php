<?php

namespace PHPCanvas\Logs;

class WriteStdOut implements LogWriteInterface
{
	private mixed $fp;

	public function __construct()
	{
		$this->fp = defined('STDOUT') ? STDOUT : fopen('php://stdout', 'w');
	}

	public function write(string $log, string $type = ''): void
	{
		if (is_resource($this->fp)) {
			fwrite($this->fp, ($type ? "$type:\t" : '') . $log . PHP_EOL);
		}
	}
}
