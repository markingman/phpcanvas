<?php

namespace PHPCanvas\Test;

class TestClassWithUnspecifiedArgs
{
	public function test($z): string
	{
		return is_bool($z) ? 'bool' : 'not-bool';
	}
}

