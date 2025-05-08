<?php declare(strict_types=1);

namespace PHPCanvas\Logs;

class LogFormatterString implements LogFormatterInterface
{
	public function __construct(
		private readonly LogWriteInterface $writer
	) {
	}

	/** @param array<string, mixed> $context */
	public function write(string $log, string $type = '', int $level = 0, array $context = []): void
	{
		$log = sprintf(
			"%s\t%s\t%s\t%s\t%s",
			$this->now(), $type ?: 'UNKNOWN', LogLevel::label($level), $log,
			json_encode((object)$context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}'
		);

		$this->writer->write($log, $type);
	}

	public function now(): string
	{
		return date('c');
	}
}
