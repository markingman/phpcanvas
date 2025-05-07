<?php declare(strict_types=1);

namespace PHPCanvas\Logs;

class WriteStdErr implements LogWriteInterface
{
	private mixed $fp;

	public function __construct()
	{
		$this->fp = defined('STDERR') ? STDERR : fopen('php://stderr', 'w');
	}

	public function write(string $log, string $type = ''): void
	{
		if (is_resource($this->fp)) {
			fwrite($this->fp, $log . PHP_EOL);
		}
	}
}
