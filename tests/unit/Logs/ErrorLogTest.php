<?php

namespace PHPCanvas\Logs;

use PHPUnit\Framework\TestCase;

class WriteErrorLogTest extends TestCase
{
	protected string $logDir;

	protected function setUp(): void
	{
		$this->logDir = sys_get_temp_dir() . '/logtest_' . bin2hex(random_bytes(4));
		mkdir($this->logDir);
	}

	protected function tearDown(): void
	{
		foreach (glob($this->logDir . '/*.log') ?: [] as $file) {
			unlink($file);
		}
		rmdir($this->logDir);
	}

	public function testWriteCreatesLogFile(): void
	{
		$writer = new WriteErrorLog($this->logDir);
		$writer->write('Test log entry', 'unit_test');

		$file = $this->logDir . '/unit_test.log';
		$this->assertFileExists($file);
		$this->assertStringContainsString('Test log entry', file_get_contents($file));
	}

	public function testWriteSanitizesFilename(): void
	{
		$writer = new WriteErrorLog($this->logDir);
		$writer->write('Log data', '../tricky_path');

		// Should not write to parent directories
		$this->assertFileExists($this->logDir . '/tricky_path.log');
	}
}
