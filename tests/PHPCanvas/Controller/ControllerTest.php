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
use PHPCanvas\Scope\Scope;
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
		include_once static::$tmpdir . '/' . $class . '.php';
	}

	public function testCreate(): void
	{
		$Config = new Config;
		$Container = new Container;
		$Request = new Request;
		$Response = new Response;
		$Router = new Router;
		$Scope = new Scope;
		$Links = new Links($Router, '');
		$Dispatch = new Dispatch(
			$Container,
			$Request,
			$Router,
			$Links,'',''		
		);

		$Controller = new Controller(
			Config: $Config,
			Dispatch: $Dispatch,
			Request: $Request,
			Response: $Response,
			Scope: $Scope,
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
		$Scope = new Scope;
		$Links = new Links($Router, '');
		$Dispatch = new Dispatch(
			$Container,
			$Request,
			$Router,
			$Links,'',''		
		);

		file_put_contents(
			static::$tmpdir . "/TestController.php",
			<<<__
<?php

use PHPCanvas\Controller\Controller;

class TestController extends Controller
{
	public function testHandler(): bool
	{
		return true;
	}
}
__
		);

		static::mockAutoload('TestController');

		$Controller = new \TestController(
			Config: $Config,
			Dispatch: $Dispatch,
			Request: $Request,
			Response: $Response,
			Scope: $Scope,
		);
	
		$this->assertInstanceOf(\TestController::class, $Controller);
		$this->assertTrue($Controller->testHandler());
	}
}