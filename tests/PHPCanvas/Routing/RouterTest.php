<?php

use PHPCanvas\Routing\Router;
use PHPCanvas\Routing\RouterInterface;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
	use PHPCanvasTestHelpersTrait;

	protected $Router, $action_default, $test_routes;

	public static function setUpBeforeClass(): void
	{
		static::tmpdir_make(self::class);
	}

	public function setUp(): void
	{
		$this->action_default = 'default';
		$this->Router = new Router($this->action_default);
	}

	public function testCreate()
	{
		$this->assertInstanceOf(RouterInterface::class, $this->Router);
	}
	
	public function testDefaultAction()
	{
		$this->assertEquals($this->Router->get_action_default(), $this->action_default);
	}

	public function testEmptyRoute()
	{
		$path = '/';
		$path2 = '';
		$path3 = '//';
		$routes = [
			'empty' => [
				'path' => '/',
				'controller' => 'App\\Controller\\Empty'
			],
		];
		
		$exp_res = [
			$routes['empty']['controller'],
			$this->action_default,
			[]
		];

		$res = $this->Router->add_route('empty', $routes['empty']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertEquals($res, $exp_res);

		$res = $this->Router->get_rewrite('empty');
		$this->assertEquals($res, $path);

		$res = $this->Router->get_route('GET', $path2);
		$this->assertEquals($res, $exp_res);

		$res = $this->Router->get_route('GET', $path3);
		$this->assertEquals($res, $exp_res);
	}

	public function testSimpleRoute()
	{
		$path = '/simple';
		$routes = [
			'simple' => [
				'path' => '/simple',
				'controller' => 'App\\Controller\\Simple'
			]
		];

		$res = $this->Router->add_route('simple', $routes['simple']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertEquals($res, [
			$routes['simple']['controller'],
			$this->action_default,
			[]
		]);

		$res = $this->Router->get_rewrite('simple');
		$this->assertEquals($res, $path);
	}

	public function testSimpleVarRoute()
	{
		$path = '/simple-var/123';
		$routes = [
			'simple-var' => [
				'path' => 'simple-var/{id}',
				'controller' => 'App\\Controller\\SimpleVar'
			]
		];

		$res = $this->Router->add_route('simple-var', $routes['simple-var']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertEquals($res, [
			$routes['simple-var']['controller'],
			$this->action_default,
			['id' => '123']
		]);

		$res = $this->Router->get_rewrite('simple-var', ['id' => '123']);
		$this->assertEquals($res, $path);
	}

	public function testMultiVarRoute()
	{
		$path = '/multi-var/123/test/abc/xyz';
		$routes = [
			'multi-var' => [
				'path' => 'multi-var/{var1}/test/{var2}/{var3}',
				'controller' => 'App\\Controller\\MultiVar'
			]
		];
		$vars = ['var1' => '123', 'var2' => 'abc', 'var3' => 'xyz'];

		$res = $this->Router->add_route('multi-var', $routes['multi-var']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertEquals($res, [
			$routes['multi-var']['controller'],
			$this->action_default,
			$vars
		]);

		$res = $this->Router->get_rewrite('multi-var', $vars);
		$this->assertEquals($res, $path);
	}
	
	public function testSimpleMethodRoute()
	{
		$path = '/method';
		$routes = [
			'method' => [
				'path' => '/method',
				'controller' => 'App\\Controller\\Method',
				'method' => 'POST'
			]
		];

		$res = $this->Router->add_route('method', $routes['method']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertFalse($res);

		$res = $this->Router->get_route('PATCH', $path);
		$this->assertFalse($res);

		$res = $this->Router->get_route('HEAD', $path);
		$this->assertFalse($res);

		$res = $this->Router->get_route('POST', $path);
		$this->assertEquals($res, [
			$routes['method']['controller'],
			$this->action_default,
			[]
		]);

		$res = $this->Router->get_rewrite('method');
		$this->assertEquals($res, $path);
	}

	public function testMultiMethodRoute()
	{
		$path = '/method';
		$routes = [
			'method' => [
				'path' => '/method',
				'controller' => 'App\\Controller\\Method',
				'method' => ['POST', 'PUT', 'PATCH']
			]
		];

		$res = $this->Router->add_route('method', $routes['method']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertFalse($res);

		$expected = [
			$routes['method']['controller'],
			$this->action_default,
			[]
		];

		$res = $this->Router->get_route('PATCH', $path);
		$this->assertEquals($res, $expected);

		$res = $this->Router->get_route('PUT', $path);
		$this->assertEquals($res, $expected);

		$res = $this->Router->get_route('POST', $path);
		$this->assertEquals($res, $expected);

		$res = $this->Router->get_rewrite('method');
		$this->assertEquals($res, $path);
	}
	
	public function testSimpleAction()
	{
		$path = '/simple/action';
		$routes = [
			'simple' => [
				'path' => '/simple/action',
				'controller' => 'App\\Controller\\Simple',
				'action' => 'simple_action',
			]
		];

		$res = $this->Router->add_route('simple', $routes['simple']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertEquals($res, [
			$routes['simple']['controller'],
			$routes['simple']['action'],
			[]
		]);

		$res = $this->Router->get_rewrite('simple');
		$this->assertEquals($res, $path);
	}

	public function testCallback()
	{
		$path = '/callback';
		$routes = [
			'callback' => [
				'path' => '/callback',
				'callback' => RouterTest::class . '::__test_callback_function'
			]
		];

		$res = $this->Router->add_route('callback', $routes['callback']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertEquals($res, false);

		$res = $this->Router->get_rewrite('callback');
		$this->assertEquals($res, $path);
	}

	public function testCallbackRoute()
	{
		$path = '/callback';
		$routes = [
			'callback' => [
				'path' => '/callback',
				'callback' => RouterTest::class . '::__test_callback_route_function'
			]
		];

		$res = $this->Router->add_route('callback', $routes['callback']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertEquals($res, false);

		$res = $this->Router->get_route('POST', $path);
		$this->assertEquals($res, [
			'App\\Controller\\Callback',
			'callback_action',
			['var1' => 'ONE', 'var2' => 'TWO']
		]);

		$res = $this->Router->get_rewrite('callback');
		$this->assertEquals($res, $path);
	}

	public function testCallbackMethod()
	{
		$path = '/callback';
		$routes = [
			'callback' => [
				'path' => '/callback',
				'method' => ['POST', 'PUT'],
				'callback' => RouterTest::class . '::__test_callback_method_function'
			]
		];

		$res = $this->Router->add_route('callback', $routes['callback']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path);
		$this->assertEquals($res, false);

		$res = $this->Router->get_route('POST', $path);
		$this->assertEquals($res, [
			'App\\Controller\\Callback',
			'callback_action',
			[]
		]);

		$res = $this->Router->get_rewrite('callback');
		$this->assertEquals($res, $path);
	}

	/*public function testPriority()
	{
		$path1 = '/priority/foo';
		$path2 = '/priority/bar';
		$routes = [
			'priority2' => [
				'path' => '/priority/{var}',
				'controller' => 'App\\Controller\\Priority2',
			],
			'priority1' => [
				'path' => '/priority/foo',
				'controller' => 'App\\Controller\\Priority1',
				'priority' => 1
			],
		];

		$res = $this->Router->add_route('priority2', $routes['priority2']);
		$this->assertTrue($res);

		$res = $this->Router->add_route('priority1', $routes['priority1']);
		$this->assertTrue($res);

		$res = $this->Router->get_route('GET', $path1);
		$this->assertEquals($res, [
			'App\\Controller\\Priority2',
			$this->action_default,
			['var' => 'foo']
		]);

		$res = $this->Router->get_route('GET', $path2);
		$this->assertEquals($res, [
			'App\\Controller\\Priority2',
			$this->action_default,
			['var' => 'bar']
		]);

		$this->Router->sort_priority();

		$res = $this->Router->get_route('GET', $path1);
		$this->assertEquals($res, [
			'App\\Controller\\Priority1',
			$this->action_default,
			[]
		]);

		$res = $this->Router->get_route('GET', $path2);
		$this->assertEquals($res, [
			'App\\Controller\\Priority2',
			$this->action_default,
			['var' => 'bar']
		]);
	}*/

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

	public static function __test_callback_method_function(string $method, array $route, array $m, string $url)
	{
		if (!ROUTER::test_route_method($method, $route)) {
			return false;
		}

		$controller = 'App\\Controller\\Callback';
		$action = 'callback_action';
		$vars = [];

		return [$controller, $action, $vars];
	}

	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
	}
}