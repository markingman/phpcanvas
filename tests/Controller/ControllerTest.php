<?php

namespace PHPCanvas\Controller;

use PHPCanvas\TestHelpersTrait;
use PHPUnit\Framework\TestCase;
use PHPCanvas\Config;
use PHPCanvas\Routing\Dispatch;
use PHPCanvas\Routing\Router;
use PHPCanvas\Routing\Links;
use PHPCanvas\Http\Request;
use PHPCanvas\Http\Response;
// use PHPCanvas\Scope\Scope;
use PHPCanvas\Container;

class ControllerTest extends TestCase
{
	use TestHelpersTrait;

	public static function setUpBeforeClass(): void
	{
		static::tmpdir_make();
	}

	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
	}

	public static function mockAutoload($class): void
	{
		$class = str_replace('PHPCanvas\\fixtures\\', '', $class);
		$class = str_replace('\\', '/', $class);
		include_once __DIR__ . '/../fixtures/' . $class . '.php';
	}

	public function testCreate(): void
	{
		$Config = new Config;
		$Container = new Container;
		$Request = new Request;
		$Response = new Response;
		$Router = new Router;
		$Links = new Links($Router, '');
		$Dispatch = new Dispatch(
			Container: $Container,
			Request: $Request,
			Response: $Response,
			Router: $Router,
			Links: $Links,
			action_prefix: '',
			action_suffix: '',
		);

		$Controller = new Controller(
			Config: $Config,
			Dispatch: $Dispatch,
			Request: $Request,
			Response: $Response,
		);
	
		$this->assertInstanceOf(Controller::class, $Controller);
	}

	public function testCanExtendController(): void
	{
		$Config = new Config;
		$Container = new Container;
		$Request = new Request;
		$Response = new Response;
		$Router = new Router;
		$Links = new Links($Router, '');
		$Dispatch = new Dispatch(
			Container: $Container,
			Request: $Request,
			Response: $Response,
			Router: $Router,
			Links: $Links,
			action_prefix: '',
			action_suffix: '',
		);

		static::mockAutoload('PHPCanvas\\fixtures\\Controller\\TestController');

		$Controller = new \PHPCanvas\fixtures\Controller\TestController(
			Config: $Config,
			Dispatch: $Dispatch,
			Request: $Request,
			Response: $Response,
		);
	
		$this->assertInstanceOf(\PHPCanvas\fixtures\Controller\TestController::class, $Controller);
		$this->assertTrue($Controller->testHandler());
	}

// TODO this is for dispatch test
// 	public function testInvokeController(): void
// 	{
// 		$Config = new Config;
// 		$Container = new Container;
// 		$Request = new Request;
// 		$Response = new Response;
// 		$Router = new Router;
// 		$Links = new Links($Router, '');
// 		$Dispatch = new Dispatch(
// 			Container: $Container,
// 			Request: $Request,
// 			Response: $Response,
// 			Router: $Router,
// 			Links: $Links,
// 			action_prefix: '',
// 			action_suffix: '',
// 		);
// 
// 		static::mockAutoload('PHPCanvas\\fixtures\\Controller\\TestInvokeController');
// 
// 		$Controller = new \PHPCanvas\fixtures\Controller\TestInvokeController(
// 			Config: $Config,
// 			Dispatch: $Dispatch,
// 			Request: $Request,
// 			Response: $Response,
// 		);
// 	
// 		$this->assertTrue($Controller->testHandler());
// 	}
}
