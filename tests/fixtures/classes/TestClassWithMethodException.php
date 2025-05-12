<?php

namespace PHPCanvas\Test;

class TestClassWithMethodException
{
	public function test(): void
	{
		throw new RuntimeException('Test method exception');
	}
}
