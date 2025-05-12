<?php

namespace PHPCanvas\Logs;

use PHPUnit\Framework\TestCase;

class WriteStdErrTest extends TestCase
{
	public function testWriteToStdErr(): void
	{
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

	public function testFWrite(): void
	{
		$WriteStdErr = new class() extends WriteStdErr {
			public function __construct()
			{
				$this->fp = tmpfile();
			}

			public function testFp(): string
			{
				$contents = '';
				rewind($this->fp);
				$contents = stream_get_contents($this->fp);
				fclose($this->fp);

				return $contents;
			}
		};

		$WriteStdErr->write('Test message', 'CLI');

		$this->assertSame('Test message', trim($WriteStdErr->testFp()));
	}
}
