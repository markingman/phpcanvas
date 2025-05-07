<?php declare(strict_types=1);

namespace PHPCanvas\Logs;

interface LogWriteInterface
{
	public function write(string $log, string $type = ''): void;
}
