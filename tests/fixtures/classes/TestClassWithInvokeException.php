<?php

namespace PHPCanvas\Test;

class TestClassWithInvokeException
{
	public function __invoke()
	{
		throw new RuntimeException('Test runtime exception');
	}

	public function test(): true
	{
		return true;
	}
}
