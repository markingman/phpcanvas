<?php

namespace PHPCanvas\Logs;

use PHPUnit\Framework\TestCase;

class WriteStdOutTest extends TestCase
{
	public function testWriteToStdOut(): void
	{
		$writer = new WriteStdOut();

		ob_start();
		$writer->write('Hello world', 'CLI');
		$output = ob_get_clean();

		$this->assertStringContainsString('CLI:', $output);
		$this->assertStringContainsString('Hello world', $output);
	}
}
