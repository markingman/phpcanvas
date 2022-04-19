<?php

namespace PHPCanvas\Logs;

interface LogWriteInterface
{
	public function write(string $log, string $type = '');
}
