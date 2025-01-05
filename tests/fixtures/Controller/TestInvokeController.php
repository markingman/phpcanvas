<?php

namespace PHPCanvas\fixtures\Controller;

use PHPCanvas\Controller\Controller;

class TestInvokeController extends Controller
{
	private bool $var = false;

	public function __invoke()
	{
		$this->var = true;	
	}

	public function testHandler(): bool
	{
		return $this->var;
	}
}
