<?php

namespace PHPCanvas\Routing;

use Exception;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
	protected Router $Router;

	/**
	 * @param array<string> $m
	 * @return false|array{string, string, array<string, string>}
	 * @throws Exception
	 */
	public static function __test_callback_function(string $method, Route $route, array $m, string $url): array|false
	{
		return false;
	}

	/**
	 * @param array<string> $m
	 * @return false|array{string, string, array<string, string>}
	 * @throws Exception
	 */
	public static function __test_callback_route_function(string $method, Route $route, array $m, string $url): array|false
	{
		if ($method !== 'POST') {
			return false;
		}

		$controller = 'App\\Controller\\Callback';
		$action = 'callback_action';
		$vars = ['var1' => 'ONE', 'var2' => 'TWO'];

		return [$controller, $action, $vars];
	}

	/**
	 * @param array<string> $m
	 * @return false|array{string, string, array<string, string>}
	 * @throws Exception
	 */
	public static function __test_callback_method_function(string $method, Route $route, array $m, string $url): false|array
	{
		if (!Router::is_route_method($method, $route->method)) {
			return false;
		}

		$controller = 'App\\Controller\\Callback';
		$action = 'callback_action';
		$vars = [];

		return [$controller, $action, $vars];
	}

	public function setUp(): void
	{
		$this->Router = new Router('default');
	}

	public function testCreate(): void
	{
		$this->assertInstanceOf(RouterInterface::class, $this->Router);
	}

	public function testAddRouteNoPathFailure(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('ROUTER_NO_PATH; No path set for route');

		$this->Router->add_route(name: 'test', path: '');
	}

	public function testAddRouteNoControllerFailure(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('ROUTER_NO_CONTROLLER; No controller set for route');

		$this->Router->add_route(name: 'test', path: '/');
	}

	public function testGetActionDefault(): void
	{
		$res = $this->Router->get_action_default();
		$this->assertEquals('default', $res);
	}

	public function testRouteEmptyRoute(): void
	{
		$route = [
			'path' => '/',
			'controller' => 'App\\Controller\\Test'
		];

		$exp = [
			'controller' => 'App\\Controller\\Test',
			'action' => 'default',
			'vars' => []
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->match_route('GET', '');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->match_route('GET', '//');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->match_route('GET', '///');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_rewrite('test');
		$this->assertEquals('/', $res, 'Should get expected rewrite from link name');
	}

	public function testRouteSimpleRoute(): void
	{
		$route = [
			'path' => '/simple',
			'controller' => 'App\\Controller\\Simple'
		];

		$exp = [
			'controller' => 'App\\Controller\\Simple',
			'action' => 'default',
			'vars' => []
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/simple');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->match_route('GET', '/simple/');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->match_route('GET', '/simple///');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->match_route('GET', '///simple///');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->get_rewrite('test');
		$this->assertEquals('/simple', $res, 'Should get expected rewrite from link name');
	}

	public function testRouteWithScopeResolutionOperatorSyntax(): void
	{
		$route = [
			'path' => '/simple',
			'controller' => 'App\\Controller\\Simple::method_name'
		];

		$exp = [
			'controller' => 'App\\Controller\\Simple',
			'action' => 'method_name',
			'vars' => []
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/simple');
		$this->assertEquals($exp, $res, 'Should get expected route from path');
	}

	public function testRouteWithAnyWildcardSyntax(): void
	{
		$route = [
			'path' => '/simple/path**',
			'controller' => 'App\\Controller\\Simple'
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller']);
		$this->assertTrue($res, 'Should add route');

		$exp = [
			'controller' => 'App\\Controller\\Simple',
			'action' => 'default',
			'vars' => []
		];

		$res = $this->Router->match_route('GET', '/simple/path');
		$this->assertEquals($exp, $res, 'Should get expected route from path');

		$res = $this->Router->match_route('GET', '/simple/path123');
		$this->assertEquals($exp, $res, 'Should get expected route from path');
	}

	public function testRouteWithSetIndex(): void
	{
// 		$route = [
// 			'path' => '/simple/path/example/{var}',
// 			'controller' => 'App\\Controller\\Simple',
// // 			'index' => 'simple',
// 		];
//
// 		$this->Router->add_route('test', $route);
// 		$res = $this->Router->dump();
//
// 		$exp = array(
//   'iname' =>
//   array (
//     'test' => 0,
//   ),
//   'routes' =>
//   array (
//     0 =>
//     array (
//       0 => 'App\\Controller\\Simple',
//       1 =>
//       array (
//         'var' => '',
//       ),
//       2 => 'default',
//       4 => 0,
//       7 => 'simple/path/example/%s',
//       8 => 'test',
//       5 => '~^simple/path/example/([^/]+)$~',
//     ),
//   ),
//   'index' =>
//   array (
//     'simple' =>
//     array (
//       0 => 0,
//     ),
//   ));
// 		$this->assertEquals($exp, $res, 'Should set default index');
// 		$this->Router->delete_route('test');


		$route = [
			'path' => '/simple/path/example/{var}',
			'controller' => 'App\\Controller\\Simple',
			'index' => 'simple/path/example',
		];

		$this->Router->add_route(
			name: 'test', path: $route['path'], controller: $route['controller'],
			index: $route['index'],
		);
		$res = $this->Router->dump();

		$exp = [
			'iname' =>
				[
					'test' => 0,
				],
			'routes' =>
				[
					0 => new Route(
						controller: 'App\\Controller\\Simple',
						vars: [
							'var' => '',
						],
						action: 'default',
						method: 0,
						sprintf: 'simple/path/example/%s',
						name: 'test',
						regx: '~^simple/path/example/([^/]+)$~',
						callback: null,
					),
				],
			'index' =>
				[
					'simple/path/example' =>
						[
							0 => 0,
						],
				]
		];

		$this->assertEquals($exp, $res, 'Should set specified index');
	}

	public function testRouteSimpleVarRoute(): void
	{
		$route = [
			'path' => 'test/{id}',
			'controller' => 'App\\Controller\\Test'
		];

		$exp = [
			'controller' => 'App\\Controller\\Test',
			'action' => 'default',
			'vars' => ['id' => '123']
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/test/123');
		$this->assertEquals($exp, $res);

		$res = $this->Router->match_route('GET', '/test/123//');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('test', ['id' => '123']);
		$this->assertEquals('/test/123', $res);

		$res = $this->Router->get_rewrite('test', ['id' => 'abc']);
		$this->assertEquals('/test/abc', $res);
	}

	public function testRouteMultiVarRoute(): void
	{
		$route = [
			'path' => 'test/{var1}/test/{var2}/{var3}',
			'controller' => 'App\\Controller\\MultiVar'
		];

		$exp = [
			'controller' => 'App\\Controller\\MultiVar',
			'action' => 'default',
			'vars' => ['var1' => '123', 'var2' => 'abc', 'var3' => 'xyz']
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/test/123/test/abc/xyz');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('test', ['var1' => '123', 'var2' => 'abc', 'var3' => 'xyz']);
		$this->assertEquals('/test/123/test/abc/xyz', $res);

		$res = $this->Router->get_rewrite('test', ['var1' => 'aaa', 'var2' => '999', 'var3' => '---']);
		$this->assertEquals('/test/aaa/test/999/---', $res);
	}

	public function testRouteSimpleMethodRoute(): void
	{
		$route = [
			'path' => '/test',
			'controller' => 'App\\Controller\\Test',
			'method' => 'POST'
		];

		$exp = [
			'controller' => 'App\\Controller\\Test',
			'action' => 'default',
			'vars' => []
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller'], method: $route['method']);
		$this->assertTrue($res, 'Should add route');

		foreach (array_keys(Router::METHODS) as $test) {
			if ($test === 'POST') {
				continue;
			}
			$res = $this->Router->match_route($test, '/test');
			$this->assertFalse($res);
		}

		$res = $this->Router->match_route('POST', '/test');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('test');
		$this->assertEquals('/test', $res);
	}

	public function testRouteMultiMethodRoute(): void
	{
		$path = '/method';
		$route = [
			'path' => '/method',
			'controller' => 'App\\Controller\\Test',
			'method' => ['POST', 'PUT', 'PATCH']
		];

		$res = $this->Router->add_route(name: 'method', path: $route['path'], controller: $route['controller'], method: $route['method']);
		$this->assertTrue($res, 'Should add route');

		foreach (array_keys(Router::METHODS) as $test) {
			if (in_array($test, ['POST', 'PUT', 'PATCH'])) {
				continue;
			}
			$res = $this->Router->match_route($test, '/method');
			$this->assertFalse($res);
		}

		$exp = [
			'controller' => 'App\\Controller\\Test',
			'action' => 'default',
			'vars' => []
		];

		$res = $this->Router->match_route('PATCH', $path);
		$this->assertEquals($exp, $res);

		$res = $this->Router->match_route('PUT', $path);
		$this->assertEquals($exp, $res);

		$res = $this->Router->match_route('POST', $path);
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('method');
		$this->assertEquals($res, $path);
	}

	public function testRouteSimpleAction(): void
	{
		$route = [
			'path' => '/simple/action',
			'controller' => 'App\\Controller\\Simple',
			'action' => 'simple_action',
		];

		$exp = [
			'controller' => 'App\\Controller\\Simple',
			'action' => 'simple_action',
			'vars' => []
		];

		$res = $this->Router->add_route(name: 'simple', path: $route['path'], controller: $route['controller'], action: $route['action']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/simple/action');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('simple');
		$this->assertEquals('/simple/action', $res);
	}

	public function testRouteRewriteWithQueryVars(): void
	{
		$route = [
			'path' => '/with/queries',
			'controller' => 'App\\Controller\\TestClass',
			'action' => 'test_action',
		];

		$exp = [
			'controller' => 'App\\Controller\\TestClass',
			'action' => 'test_action',
			'vars' => []
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller'], action: $route['action']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/with/queries');
		$this->assertEquals($exp, $res, 'Should get expected query var route');

		$res = $this->Router->get_rewrite('test', ['var1' => '1', 'var2' => '2']);
		$this->assertEquals('/with/queries?var1=1&var2=2', $res, 'Should write expected link');
	}

	public function testRouteRewriteWithPathVarsAndQueryVars(): void
	{
		$route = [
			'path' => '/with/{var1}/{var2}/queries',
			'controller' => 'App\\Controller\\TestClass',
			'action' => 'test_action',
		];

		$exp = [
			'controller' => 'App\\Controller\\TestClass',
			'action' => 'test_action',
			'vars' => ['var1' => 'a', 'var2' => 'b']
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller'], action: $route['action']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/with/a/b/queries');
		$this->assertEquals($exp, $res, 'Should get expected query var route');

		$res = $this->Router->get_rewrite('test', ['var1' => 'a', 'var2' => 'b', 'var3' => 'c']);
		$this->assertEquals('/with/a/b/queries?var3=c', $res, 'Should write expected link');

		$res = $this->Router->get_rewrite('test', ['var1' => '1', 'var2' => '2', 'var3' => '3', 'var4' => '4']);
		$this->assertEquals('/with/1/2/queries?var3=3&var4=4', $res, 'Should write expected link');
	}

	public function testRouteCallback(): void
	{
		$route = [
			'path' => '/callback',
			'callback' => function (string $method, Route $route, array $m, string $url) {
				$class = RouterTest::class;

				return $class::__test_callback_function($method, $route, $m, $url);
			}
		];

		$res = $this->Router->add_route(name: 'callback', path: $route['path'], callback: $route['callback']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/callback');
		$this->assertFalse($res, 'Callback can resolve FALSE itself');

		$res = $this->Router->get_rewrite('callback');
		$this->assertEquals('/callback', $res);
	}

	public function testRouteCallbackRoute(): void
	{
		$route = [
			'path' => '/callback',
			'callback' => function (string $method, Route $route, array $m, string $url) {
				$class = RouterTest::class;

				return $class::__test_callback_route_function($method, $route, $m, $url);
			}
		];

		$exp = [
			'controller' => 'App\\Controller\\Callback',
			'action' => 'callback_action',
			'vars' => ['var1' => 'ONE', 'var2' => 'TWO']
		];

		$res = $this->Router->add_route(name: 'callback', path: $route['path'], callback: $route['callback']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/callback');
		$this->assertFalse($res);

		$res = $this->Router->match_route('POST', '/callback');
		$this->assertEquals($exp, $res);

		$res = $this->Router->get_rewrite('callback');
		$this->assertEquals('/callback', $res);
	}

	public function testRouteCallbackWithMethod(): void
	{
		$route = [
			'path' => '/callback',
			'method' => ['POST', 'PUT'],
			'callback' => function (string $method, Route $route, array $m, string $url) {
				$class = RouterTest::class;

				return $class::__test_callback_method_function($method, $route, $m, $url);
			}
		];

		$exp = [
			'controller' => 'App\\Controller\\Callback',
			'action' => 'callback_action',
			'vars' => []
		];

		$res = $this->Router->add_route(name: 'callback', path: $route['path'], callback: $route['callback'], method: $route['method']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/callback');
		$this->assertFalse($res);

		$res = $this->Router->match_route('POST', '/callback');
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
			'controller' => 'App\\Controller\\Test',
			'action' => 'default',
			'vars' => []
		];

		$res = $this->Router->add_route(name: 'test', path: $route['path'], controller: $route['controller']);
		$this->assertTrue($res, 'Should add route');

		$res = $this->Router->match_route('GET', '/test');
		$this->assertEquals($exp, $res);

		$res = $this->Router->delete_route('test');
		$this->assertTrue($res);

		$res = $this->Router->match_route('GET', '/test');
		$this->assertFalse($res);

		$res = $this->Router->delete_route('test');
		$this->assertFalse($res);
	}

	public function testGetRoutes(): void
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
			$callback = $route['callback'] ?? null;
			$this->Router->add_route(
				name: $name,
				path: $route['path'],
				controller: $route['controller'] ?? '',
				action: $route['action'] ?? null,
				callback: $callback ? function (string $method, Route $route, array $m, string $url) use ($callback) {
					return $callback($method, $route, $m, $url);
				} : null,
				vars: $route['vars'] ?? null,
				method: $route['method'] ?? null,
			);
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
				'controller' => '',
				'action' => 'default',
				'method' => ['POST', 'PUT'],
				'callback' => function (string $method, Route $route, array $m, string $url) {
				},
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

	// TODO: move these to fixtures

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
				0 => new Route(
					controller: 'App\Controller\Test',
					vars: [],
					action: 'default',
					method: 0,
					sprintf: 'test',
					name: 'test',
					regx: '~^test$~',
				),
				1 => new Route(
					controller: 'App\Controller\Test2',
					vars: [],
					action: 'foo',
					method: 0,
					sprintf: 'test2/foo',
					name: 'test2',
					regx: '~^test2/foo$~',
				),
				2 => new Route(
					controller: 'App\Controller\Test3',
					vars: ['var1' => ''],
					action: 'default',
					method: Router::METHODS['GET'] + Router::METHODS['POST'],
					sprintf: 'test3/foo/%s',
					name: 'test3',
					regx: '~^test3/foo/([^/]+)$~',
				),
			],
			'index' => [
				'test2' => [1],
				'test3' => [2]
			],
		];

		foreach ($routes as $name => $route) {
			$this->Router->add_route(
				name: $name,
				path: $route['path'],
				controller: $route['controller'],
				action: $route['action'] ?? null,
				method: $route['method'] ?? null,
			);
		}

		$res = $this->Router->dump();
		$this->assertEquals($exp, $res);
	}
}
