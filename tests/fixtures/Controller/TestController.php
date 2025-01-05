<?php

namespace PHPCanvas\fixtures\Controller;

use PHPCanvas\Controller\Controller;

class TestController extends Controller
{
	public function testHandler(): bool
	{
		return true;
	}
}
