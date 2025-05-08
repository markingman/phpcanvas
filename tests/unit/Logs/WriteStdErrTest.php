<?php

namespace PHPCanvas\Logs;

use PHPUnit\Framework\TestCase;

class WriteStdErrTest extends TestCase
{
	public function testWriteToStdErr(): void
	{
		$writer = new WriteStdErr();

		$WriteStdErr = new class extends WriteStdErr {
			public readonly string $test;

			protected function fwrite(string $log): void
			{
				$this->test = $log;
			}
		};

		$WriteStdErr->write('Test message', 'CLI');

		$this->assertSame('Test message' . PHP_EOL, $WriteStdErr->test);
	}
}
