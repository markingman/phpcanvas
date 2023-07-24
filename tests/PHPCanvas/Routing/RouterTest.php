<?php

use PHPCanvas\Routing\Router;
use PHPCanvas\Routing\RouterInterface;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
	protected Router $Router;

	public function setUp(): void
	{
		$this->Router = new Router('default');
	}

	public function testCreate(): void
	{
		$this->assertInstanceOf(RouterInterface::class, $this->Router);
	}

	public function testGetActionDefault(): void
	{
		$res = $this->Router->get_action_default();
		$this->assertEquals('default', $res);
	}

	public function testRouteEmptyRoute()
	{
		$route = [
			'path' => '/',
			'controller' => 'App\\Controller\\Test'
		];

		$exp = [
			'App\\Controller\\Test',
			'default',
			[]
		];

		$res = $this->Router->add_route('test', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_route('GET', '');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_route('GET', '//');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_route('GET', '///');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_rewrite('test');
		$this->assertEquals('/', $res, 'Should get expected rewrite from link name');
	}

	public function testRouteSimpleRoute()
	{
		$route = [
			'path' => '/simple',
			'controller' => 'App\\Controller\\Simple'
		];

		$exp = [
			'App\\Controller\\Simple',
			'default',
			[]
		];

		$res = $this->Router->add_route('test', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/simple');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_route('GET', '/simple/');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_route('GET', '/simple///');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_route('GET', '///simple///');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_rewrite('test');
		$this->assertEquals('/simple', $res, 'Should get expected rewrite from link name');
	}

	public function testRouteSimpleVarRoute()
	{
		$route = [
			'path' => 'test/{id}',
			'controller' => 'App\\Controller\\Test'
		];

		$exp = [
			'App\\Controller\\Test',
			'default',
			['id' => '123']
		];

		$res = $this->Router->add_route('test', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/test/123');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_route('GET', '/test/123//');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('test', ['id' => '123']);
		$this->assertEquals('/test/123', $res);

		$res = $this->Router->get_rewrite('test', ['id' => 'abc']);
		$this->assertEquals('/test/abc', $res);
	}

	public function testRouteMultiVarRoute()
	{
		$route = [
			'path' => 'test/{var1}/test/{var2}/{var3}',
			'controller' => 'App\\Controller\\MultiVar'
		];

		$exp = [
			'App\\Controller\\MultiVar',
			'default',
			['var1' => '123', 'var2' => 'abc', 'var3' => 'xyz']
		];

		$res = $this->Router->add_route('test', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/test/123/test/abc/xyz');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('test', ['var1' => '123', 'var2' => 'abc', 'var3' => 'xyz']);
		$this->assertEquals('/test/123/test/abc/xyz', $res);

		$res = $this->Router->get_rewrite('test', ['var1' => 'aaa', 'var2' => '999', 'var3' => '---']);
		$this->assertEquals('/test/aaa/test/999/---', $res);
	}

	public function testRouteSimpleMethodRoute()
	{
		$route = [
			'path' => '/test',
			'controller' => 'App\\Controller\\Test',
			'method' => 'POST'
		];

		$exp = [
			'App\\Controller\\Test',
			'default',
			[]
		];

		$res = $this->Router->add_route('test', $route);
		$this->assertTrue($res, 'Should add route');

		foreach (array_keys(Router::METHODS) as $test) {
			if ($test === 'POST') {
				continue;
			}
			$res = $this->Router->get_route($test, '/test');
			$this->assertFalse($res);
		}

		$res = $this->Router->get_route('POST', '/test');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('test');
		$this->assertEquals('/test', $res);
	}

	public function testRouteMultiMethodRoute()
	{
		$path = '/method';
		$route = [
			'path' => '/method',
			'controller' => 'App\\Controller\\Test',
			'method' => ['POST', 'PUT', 'PATCH']
		];

		$res = $this->Router->add_route('method', $route);
		$this->assertTrue($res, 'Should add route');

		foreach (array_keys(Router::METHODS) as $test) {
			if (in_array($test, ['POST', 'PUT', 'PATCH'])) {
				continue;
			}
			$res = $this->Router->get_route($test, '/method');
			$this->assertFalse($res);
		}

		$exp = [
			'App\\Controller\\Test',
			'default',
			[]
		];

		$res = $this->Router->get_route('PATCH', $path);
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_route('PUT', $path);
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_route('POST', $path);
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('method');
		$this->assertEquals($res, $path);
	}

	public function testRouteSimpleAction()
	{
		$route = [
			'path' => '/simple/action',
			'controller' => 'App\\Controller\\Simple',
			'action' => 'simple_action',
		];

		$exp = [
			'App\\Controller\\Simple',
			'simple_action',
			[]
		];

		$res = $this->Router->add_route('simple', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/simple/action');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('simple');
		$this->assertEquals('/simple/action', $res);
	}

	public function testRouteRewriteWithQueryVars()
	{
		$route = [
			'path' => '/with/queries',
			'controller' => 'App\\Controller\\TestClass',
			'action' => 'test_action',
		];

		$exp = [
			'App\\Controller\\TestClass',
			'test_action',
			[]
		];

		$res = $this->Router->add_route('test', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/with/queries');
		$this->assertEquals($exp, $res, 'Should get expected query var route');

		$res = $this->Router->get_rewrite('test', ['var1' => '1', 'var2' => '2']);
		$this->assertEquals('/with/queries?var1=1&var2=2', $res, 'Should write expected link');
	}

	public function testRouteRewriteWithPathVarsAndQueryVars()
	{
		$route = [
			'path' => '/with/{var1}/{var2}/queries',
			'controller' => 'App\\Controller\\TestClass',
			'action' => 'test_action',
		];

		$exp = [
			'App\\Controller\\TestClass',
			'test_action',
			['var1' => 'a', 'var2' => 'b']
		];

		$res = $this->Router->add_route('test', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/with/a/b/queries');
		$this->assertEquals($exp, $res, 'Should get expected query var route');

		$res = $this->Router->get_rewrite('test', ['var1' => 'a', 'var2' => 'b', 'var3' => 'c']);
		$this->assertEquals('/with/a/b/queries?var3=c', $res, 'Should write expected link');

		$res = $this->Router->get_rewrite('test', ['var1' => '1', 'var2' => '2', 'var3' => '3', 'var4' => '4']);
		$this->assertEquals('/with/1/2/queries?var3=3&var4=4', $res, 'Should write expected link');
	}

	public function testRouteCallback()
	{
		$route = [
			'path' => '/callback',
			'callback' => RouterTest::class . '::__test_callback_function'
		];

		$res = $this->Router->add_route('callback', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/callback');
		$this->assertFalse($res, 'Callback can resolve FALSE itself');

		$res = $this->Router->get_rewrite('callback');
		$this->assertEquals('/callback', $res);
	}

	public function testRouteCallbackRoute()
	{
		$route = [
			'path' => '/callback',
			'callback' => RouterTest::class . '::__test_callback_route_function'
		];

		$exp = [
			'App\\Controller\\Callback',
			'callback_action',
			['var1' => 'ONE', 'var2' => 'TWO']
		];

		$res = $this->Router->add_route('callback', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/callback');
		$this->assertFalse($res);

		$res = $this->Router->get_route('POST', '/callback');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('callback');
		$this->assertEquals('/callback', $res);
	}

	public function testRouteCallbackWithMethod()
	{
		$route = [
			'path' => '/callback',
			'method' => ['POST', 'PUT'],
			'callback' => RouterTest::class . '::__test_callback_method_function'
		];

		$exp = [
			'App\\Controller\\Callback',
			'callback_action',
			[]
		];

		$res = $this->Router->add_route('callback', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/callback');
		$this->assertFalse($res);

		$res = $this->Router->get_route('POST', '/callback');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('callback');
		$this->assertEquals('/callback', $res);
	}

	public function testDeleteRoute(): void
	{
		$route = [
			'path' => '/test',
			'controller' => 'App\\Controller\\Test',
		];

		$exp = [
			'App\\Controller\\Test',
			'default',
			[]
		];

		$res = $this->Router->add_route('test', $route);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->get_route('GET', '/test');
		$this->assertEquals($exp, $res);

		$res = $this->Router->delete_route('test');
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', '/test');
		$this->assertFalse($res);

		$res = $this->Router->delete_route('test');
		$this->assertFalse($res);
	}

	public function testGetRoutes()
	{
		$routes = [
			'test1' => [
				'path' => '/test',
				'controller' => 'App\\Controller\\Test',
			],
			'test2' => [
				'path' => '/test2/foo',
				'controller' => 'App\\Controller\\Test2',
				'action' => 'foo'
			],
			'test3' => [
				'path' => '/test3/foo/{var1}',
				'controller' => 'App\\Controller\\Test3',
				'method' => ['GET', 'POST'],
				'vars' => ['var1' => 'VAR1_DEFAULT', 'var2' => '']
			],
			'test4' => [
				'path' => '/callback',
				'method' => ['POST', 'PUT'],
				'callback' => RouterTest::class . '::__test_callback_method_function'
			]
		];

		foreach ($routes as $name => $route) {
			$this->Router->add_route($name, $route);
		}

		$exp = [
			'test1' => [
				'path' => '~^test$~',
				'controller' => 'App\\Controller\\Test',
				'action' => 'default',
			],
			'test2' => [
				'path' => '~^test2/foo$~',
				'controller' => 'App\\Controller\\Test2',
				'action' => 'foo',
			],
			'test3' => [
				'path' => '~^test3/foo/([^/]+)$~',
				'controller' => 'App\\Controller\\Test3',
				'action' => 'default',
				'method' => ['GET', 'POST'],
				'vars' => [
					'var1' => 'VAR1_DEFAULT',
					'var2' => '',
				]
			],
			'test4' => [
				'path' => '~^callback$~',
				'action' => 'default',
				'method' => ['POST', 'PUT'],
				'callback' => RouterTest::class . '::__test_callback_method_function',
			]
		];

		$res = $this->Router->get_routes();
		$this->assertEquals($exp, $res);
	}

	public function testRouteMethod(): void
	{
		$route_method = Router::METHODS['GET'];

		$res = Router::is_route_method('GET', $route_method);
		$this->assertTrue($res);

		foreach (array_keys(Router::METHODS) as $test) {
			if ($test === 'GET') {
				continue;
			}
			$res = Router::is_route_method($test, $route_method);
			$this->assertFalse($res, $test . ' should asssert false');
		}

		$route_method += Router::METHODS['PATCH'];

		$res = Router::is_route_method('GET', $route_method);
		$this->assertTrue($res);

		$res = Router::is_route_method('PATCH', $route_method);
		$this->assertTrue($res);

		foreach (array_keys(Router::METHODS) as $test) {
			if ($test === 'GET' or $test === 'PATCH') {
				continue;
			}
			$res = Router::is_route_method($test, $route_method);
			$this->assertFalse($res);
		}

		$route_method += Router::METHODS['POST'];

		$res = Router::is_route_method('GET', $route_method);
		$this->assertTrue($res);

		$res = Router::is_route_method('PATCH', $route_method);
		$this->assertTrue($res);

		$res = Router::is_route_method('POST', $route_method);
		$this->assertTrue($res);

		foreach (array_keys(Router::METHODS) as $test) {
			if ($test === 'GET' or $test === 'PATCH' or $test === 'POST') {
				continue;
			}
			$res = Router::is_route_method($test, $route_method);
			$this->assertFalse($res);
		}
	}

	public function getGetRouteMethods(): void
	{
		$res = $this->Router->get_route_methods(0);
		$exp = array_keys($this->Router::METHODS);
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_route_methods($this->Router::METHODS['GET'] + $this->Router::METHODS['OPTIONS']);
		$exp = ['GET', 'OPTIONS'];
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_route_methods(
			$this->Router::METHODS['GET'] +
			$this->Router::METHODS['OPTIONS'] +
			$this->Router::METHODS['POST']
		);
		$exp = ['GET', 'POST', 'OPTIONS'];
		$this->assertEquals($exp, $res);
	}

	public function testGetRouteVars(): void
	{
		preg_match('~^test3/foo/([^/]+)$~', 'test3/foo/test', $m);
		$route_vars = ['var1' => ''];
		$res = Router::get_route_vars($route_vars, $m);
		$exp = ['var1' => 'test'];
		$this->assertEquals($exp, $res);

		preg_match('~^test3/foo/([^/]+)/([^/]+)$~', 'test3/foo/test1/test2', $m);
		$route_vars = ['var1' => '', 'var2' => ''];
		$res = Router::get_route_vars($route_vars, $m);
		$exp = ['var1' => 'test1', 'var2' => 'test2'];
		$this->assertEquals($exp, $res);
	}

	public function testDump(): void
	{
		$routes = [
			'test' => [
				'path' => '/test',
				'controller' => 'App\\Controller\\Test',
			],
			'test2' => [
				'path' => '/test2/foo',
				'controller' => 'App\\Controller\\Test2',
				'action' => 'foo'
			],
			'test3' => [
				'path' => '/test3/foo/{var1}',
				'controller' => 'App\\Controller\\Test3',
				'method' => ['GET', 'POST'],
			]
		];

		$exp = [
			'iname' => [
				'test' => 0,
				'test2' => 1,
				'test3' => 2,
			],
			'routes' => [
				0 => [
					Router::CONTROLLER => 'App\Controller\Test',
					Router::VARS => [],
					Router::ACTION => 'default',
					Router::METHOD => 0,
					Router::SPRNTF => 'test',
					Router::NAME => 'test',
					Router::REGX => '~^test$~',
				],
				1 => [
					Router::CONTROLLER => 'App\Controller\Test2',
					Router::VARS => [],
					Router::ACTION => 'foo',
					Router::METHOD => 0,
					Router::SPRNTF => 'test2/foo',
					Router::NAME => 'test2',
					Router::REGX => '~^test2/foo$~',
				],
				2 => [
					Router::CONTROLLER => 'App\Controller\Test3',
					Router::VARS => ['var1' => ''],
					Router::ACTION => 'default',
					Router::METHOD => Router::METHODS['GET'] + Router::METHODS['POST'],
					Router::SPRNTF => 'test3/foo/%s',
					Router::NAME => 'test3',
					Router::REGX => '~^test3/foo/([^/]+)$~',
				],
			],
			'index' => [
				'test2' => [1],
				'test3' => [2]
			],
		];

		foreach ($routes as $name => $route) {
			$this->Router->add_route($name, $route);
		}

		$res = $this->Router->dump();
		$this->assertEquals($exp, $res);
	}

	public static function __test_callback_function(string $method, array $route, array $m, string $url): array|false
	{
		return false;
	}

	public static function __test_callback_route_function(string $method, array $route, array $m, string $url): array|false
	{
		if ($method !== 'POST') {
			return false;
		}

		$controller = 'App\\Controller\\Callback';
		$action = 'callback_action';
		$vars = ['var1' => 'ONE', 'var2' => 'TWO'];

		return [$controller, $action, $vars];
	}

	public static function __test_callback_method_function(string $method, array $route, array $m, string $url): false|array
	{
		if (!Router::is_route_method($method, $route[Router::METHOD])) {
			return false;
		}

		$controller = 'App\\Controller\\Callback';
		$action = 'callback_action';
		$vars = [];

		return [$controller, $action, $vars];
	}
}