<?php declare(strict_types=1);

namespace PHPCanvas\Logs;

use RuntimeException;

class WriteStdErr implements LogWriteInterface
{
	/** @var resource */
	protected mixed $fp;

	public function __construct()
	{
		$fp = defined('STDERR') ? STDERR : fopen('php://stderr', 'w');

		if (!is_resource($fp)) {
			throw new RuntimeException('Could not open stderr');
		}

		$this->fp = $fp;
	}

	public function write(string $log, string $type = ''): void
	{
		$this->fwrite($log . PHP_EOL);
	}

	protected function fwrite(string $log): void
	{
		if (is_resource($this->fp)) {
			fwrite($this->fp, $log . PHP_EOL);
		}
	}
}
