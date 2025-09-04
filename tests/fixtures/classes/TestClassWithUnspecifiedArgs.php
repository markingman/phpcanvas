<?php

namespace PHPCanvas\Test;

class TestClassWithUnspecifiedArgs
{
	public function test(mixed $z): string
	{
		return is_bool($z) ? 'bool' : 'not-bool';
	}
}

