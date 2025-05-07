<?php

namespace PHPCanvas\Logs;

use PHPUnit\Framework\TestCase;

class LogHandlerTest extends TestCase
{
	public function testLogDispatchesToWriter(): void
	{
		$mockWriter = $this->createMock(LogWriteInterface::class);
		$mockWriter->expects($this->once())
			->method('write')
			->with(
				$this->stringContains('INFO'),
				'test-type'
			);

		$logger = new LogHandler($mockWriter, 'test-type');
		$logger->info('Something happened');
	}

	public function testLogFallbacksToUnknown(): void
	{
		$mockWriter = $this->createMock(LogWriteInterface::class);
		$mockWriter->expects($this->once())
			->method('write')
			->with(
				$this->stringContains('UNKNOWN (0)'),
				''
			);

		$logger = new LogHandler($mockWriter);
		$logger->log('Message with unknown type', 999); // invalid level
	}
}
