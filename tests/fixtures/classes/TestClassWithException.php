<?php

namespace PHPCanvas\Test;

use RuntimeException;

class TestClassWithException
{
	public function __construct()
	{
		throw new RuntimeException('Test runtime exception');
	}
}
