<?php declare(strict_types=1);

namespace PHPCanvas\Logs;

interface LogFormatterInterface
{
	/** @param array<string, mixed> $context */
	public function write(string $log, string $type = '', int $level = 0, array $context = []): void;
}
