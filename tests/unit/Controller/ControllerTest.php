<?php

namespace PHPCanvas\Controller;

use PHPCanvas\Config;
use PHPCanvas\Http\Request;
use PHPCanvas\Http\Response;
use PHPCanvas\Routing\Dispatch;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
	public function testCanBeInstantiated(): void
	{
		$Config = new Config();
		$mockDispatch = $this->createMock(Dispatch::class);
		$mockRequest = $this->createMock(Request::class);
		$mockResponse = $this->createMock(Response::class);

		$controller = new Controller($Config, $mockDispatch, $mockRequest, $mockResponse);

		$this->assertInstanceOf(Controller::class, $controller);
	}
}
