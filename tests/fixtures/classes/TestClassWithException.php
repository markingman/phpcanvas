<?php

namespace PHPCanvas\Test;

class TestClassWithException
{
	public function __construct()
	{
		throw new RuntimeException('Test runtime exception');
	}
}
