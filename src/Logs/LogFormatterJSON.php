<?php declare(strict_types=1);

namespace PHPCanvas\Logs;

use RuntimeException;

class LogFormatterJSON implements LogFormatterInterface
{
	public function __construct(
		private readonly LogWriteInterface $writer
	) {
	}

	/** @param array<string, mixed> $context */
	public function write(string $log, string $type = '', int $level = 0, array $context = []): void
	{
		$log = json_encode([
			'timestamp' => $this->now(),
			'type' => $type ?: 'UNKNOWN',
			'level' => LogLevel::label($level),
			'message' => $log,
			'context' => (object)$context, // Cast to object for empty = {}
		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		if (!$log) {
			throw new RuntimeException('Unable to encode log');
		}

		$this->writer->write($log, $type);
	}


	public function now(): string
	{
		return date('c');
	}
}
