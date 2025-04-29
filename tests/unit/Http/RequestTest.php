<?php

namespace PHPCanvas\Http;

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
	protected array $headers = [];
	protected array $get = [];
	protected array $post = [];
	protected array $files = [];
	protected array $server = [];
	protected array $cookie = [];
	protected Request $Request;

	public function setUp(): void
	{
		$this->headers = [];
		$this->get = [];
		$this->post = [];
		$this->files = [];
		$this->server = [];
		$this->cookie = [];

		$this->Request = new Request(
			$this->headers,
			$this->get,
			$this->post,
			$this->files,
			$this->server,
			$this->cookie
		);
	}

	public function testCreate(): void
	{
		$this->assertInstanceOf(RequestInterface::class, $this->Request);
	}

	public function testSetGetHeaders(): void
	{
		$res = $this->Request->get_header('X-TEST');
		$this->assertNull($res);

		$res = $this->Request->set_headers();
		$this->assertNull($res);

		if (is_callable('xdebug_get_headers')) {
			$res = xdebug_get_headers();
			$this->assertEquals([], $res);
		}

		$this->Request->set_headers(['X-TEST' => 'test']);

		$res = $this->Request->get_header('X-TEST');
		$this->assertEquals('test', $res);

		$res = $this->Request->get_header('X-UNKOWN');
		$this->assertNull($res);
	}

	public function testSetGetGet(): void
	{
		$_GET = ['foo' => 'bar1'];
		$this->Request->set_get();

		$res = $this->Request->get_get('foo');
		$this->assertEquals('bar1', $res);

		$get = ['foo' => 'bar'];
		$this->Request->set_get($get);

		$res = $this->Request->get_get('foo');
		$this->assertEquals('bar', $res);
	}

	public function testSetGetValue(): void
	{
		$_GET = ['foo' => 'bar1'];
		$this->Request->set_get();

		$res = $this->Request->get_get('a');
		$this->assertEquals('', $res);

		$this->Request->set_get_value('a', 'B');

		$res = $this->Request->get_get('a');
		$this->assertEquals('B', $res);
	}

	public function testSetGetPost(): void
	{
		$_POST = ['foo' => 'bar1'];
		$this->Request->set_post();

		$res = $this->Request->get_post('foo');
		$this->assertEquals('bar1', $res);

		$post = ['foo' => 'bar'];
		$this->Request->set_post($post);

		$res = $this->Request->get_post('foo');
		$this->assertEquals('bar', $res);
	}

	public function testSetGetFiles(): void
	{
		$file = [
			'name' => 'test.jpg',
			'type' => 'image/jpeg',
			'tmp_name' => '/tmp/phpn3FyFr',
			'error' => 0,
			'size' => 1024,
			'full_path' => '/example/test.jpg',
		];

		$_FILES = ['foo' => $file];
		$this->Request->set_files();

		$res = $this->Request->get_file('foo');
		$this->assertEquals($file, $res);

		$files = ['foo' => $file];
		$this->Request->set_files($files);

		$res = $this->Request->get_file('foo');
		$this->assertEquals($file, $res);
	}

	public function testSetGetServer(): void
	{
		$_SERVER = ['foo' => 'bar1'];
		$this->Request->set_server();

		$res = $this->Request->get_server('foo');
		$this->assertEquals('bar1', $res);

		$server = ['foo' => 'bar'];
		$this->Request->set_server($server);

		$res = $this->Request->get_server('foo');
		$this->assertEquals('bar', $res);
	}

	public function testSetGetCookie(): void
	{
		$_COOKIE = ['foo' => 'bar1'];
		$this->Request->set_cookie();

		$res = $this->Request->get_cookie('foo');
		$this->assertEquals('bar1', $res);

		$cookie = ['foo' => 'bar'];
		$this->Request->set_cookie($cookie);

		$res = $this->Request->get_cookie('foo');
		$this->assertEquals('bar', $res);
	}

	public function testSetGetMethod(): void
	{
		$server = ['REQUEST_METHOD' => 'PATCH'];
		$this->Request->set_server($server);

		$res = $this->Request->get_method();
		$this->assertEquals('PATCH', $res);

		//$get = ['A' => 'a'];

		$this->Request->set_method('POST');

		$res = $this->Request->get_method();
		$this->assertEquals('POST', $res);
	}

	public function testSetGetPath(): void
	{
		$_SERVER['REDIRECT_URL'] = '/redirect/path';
		$this->Request->set_server();

		$res = $this->Request->get_path();
		$this->assertEquals('/redirect/path', $res);

		$this->Request->set_path('http://example.com/test/path?a=1&b=2');
		$res = $this->Request->get_path();
		$this->assertEquals('/test/path', $res);

		$this->Request->set_path('/test/path/only');
		$res = $this->Request->get_path();
		$this->assertEquals('/test/path/only', $res);
	}

	public function testSetGetUA(): void
	{
		$_SERVER['HTTP_USER_AGENT'] = 'ua-string-1';
		$this->Request->set_server();

		$res = $this->Request->get_ua();
		$this->assertEquals('ua-string-1', $res);

		$this->Request->set_ua('ua-string-2');
		$res = $this->Request->get_ua();
		$this->assertEquals('ua-string-2', $res);
	}

	public function testSetGetIP(): void
	{
		$_SERVER['REMOTE_ADDR'] = '1.1.1.1';
		$this->Request->set_server();

		$res = $this->Request->get_ip();
		$this->assertEquals('1.1.1.1', $res);

		$this->Request->set_ip('--invalid--');
		$res = $this->Request->get_ip();
		$this->assertEquals('', $res);

		$this->Request->set_ip('0.0.0.0');
		$res = $this->Request->get_ip();
		$this->assertEquals('0.0.0.0', $res);
	}

	public function testSetGetIPProxy(): void
	{
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '2.2.2.2';
		unset($_SERVER['REMOTE_ADDR']);
		$this->Request->set_server();

		$res = $this->Request->get_ip();
		$this->assertEquals('2.2.2.2', $res);
	}

	public function testSetGetReferer(): void
	{
		$_SERVER['HTTP_REFERER'] = 'http://example.com';
		$this->Request->set_server();

		$res = $this->Request->get_ref();
		$this->assertEquals('http://example.com', $res);

		$this->Request->set_ref('http://example.com/1');
		$res = $this->Request->get_ref();
		$this->assertEquals('http://example.com/1', $res);
	}

// 

	public function testSetGetIntFromGet(): void
	{
		$_GET['id'] = '1';
		$_GET['c'] = '2';
		$_GET['i'] = '-1';
		$this->Request->set_get();

		$res = $this->Request->get_int_from_get('id');
		$this->assertEquals('1', $res);

		$res = $this->Request->get_int_from_get(['a', 'b', 'c']);
		$this->assertEquals('2', $res);

		$res = $this->Request->get_int_from_get('z', 100);
		$this->assertEquals(100, $res);

		$res = $this->Request->get_int_from_get('i', min_range: -2);
		$this->assertEquals(-1, $res);
	}

	public function testSetGetIntFromPost(): void
	{
		$_POST['id'] = '1';
		$_POST['c'] = '2';
		$_POST['i'] = '-1';
		$this->Request->set_post();

		$res = $this->Request->get_int_from_post('id');
		$this->assertEquals('1', $res);

		$res = $this->Request->get_int_from_post(['a', 'b', 'c']);
		$this->assertEquals('2', $res);

		$res = $this->Request->get_int_from_post('z', 100);
		$this->assertEquals(100, $res);

		$res = $this->Request->get_int_from_post('i', min_range: -2);
		$this->assertEquals(-1, $res);
	}


	public function testSetGetVarFromGet(): void
	{
		$_GET['a'] = 'aA';
		$_GET['c'] = 'cC';
		$this->Request->set_get();

		$res = $this->Request->get_var_from_get('a');
		$this->assertEquals('aA', $res);

		$res = $this->Request->get_var_from_get(['z', 'y', 'c']);
		$this->assertEquals('cC', $res);

		$res = $this->Request->get_var_from_get('z', 'zZ');
		$this->assertEquals('zZ', $res);
	}

	public function testSetGetVarFromPost(): void
	{
		$_POST['a'] = 'aA';
		$_POST['c'] = 'cC';
		$this->Request->set_post();

		$res = $this->Request->get_var_from_post('a');
		$this->assertEquals('aA', $res);

		$res = $this->Request->get_var_from_post(['z', 'y', 'c']);
		$this->assertEquals('cC', $res);

		$res = $this->Request->get_var_from_post('z', 'zZ');
		$this->assertEquals('zZ', $res);
	}


	public function testSetGetValFromGet(): void
	{
		$_GET['a'] = 'a A';
		$_GET['c'] = 'c C';
		$this->Request->set_get();

		$res = $this->Request->get_val_from_get('a');
		$this->assertEquals('a A', $res);

		$res = $this->Request->get_val_from_get('c');
		$this->assertEquals('c C', $res);

		$res = $this->Request->get_val_from_get('z', 'z Z');
		$this->assertEquals('z Z', $res);
	}

	public function testSetGetValFromPost(): void
	{
		$_POST['a'] = 'a A';
		$_POST['c'] = 'c C';
		$this->Request->set_post();

		$res = $this->Request->get_val_from_post('a');
		$this->assertEquals('a A', $res);

		$res = $this->Request->get_val_from_post('c');
		$this->assertEquals('c C', $res);

		$res = $this->Request->get_val_from_post('z', 'z Z');
		$this->assertEquals('z Z', $res);
	}


	public function testSetGetSelFromGet(): void
	{
		$_GET['a'] = 'aA';
		$_GET['c'] = 'cC';
		$this->Request->set_get();

		$res = $this->Request->get_sel_from_get('x');
		$this->assertEquals('', $res);

		$res = $this->Request->get_sel_from_get('a', ['aA', 'bB']);
		$this->assertEquals('aA', $res);

		$res = $this->Request->get_sel_from_get('c', ['cC']);
		$this->assertEquals('cC', $res);

		$res = $this->Request->get_sel_from_get('z', ['zZ'], 'zZ');
		$this->assertEquals('zZ', $res);
	}

	public function testSetGetSelFromPost(): void
	{
		$_POST['a'] = 'aA';
		$_POST['c'] = 'cC';
		$this->Request->set_post();

		$res = $this->Request->get_sel_from_post('x');
		$this->assertEquals('', $res);

		$res = $this->Request->get_sel_from_post('a', ['aA', 'bB']);
		$this->assertEquals('aA', $res);

		$res = $this->Request->get_sel_from_post('c', ['cC']);
		$this->assertEquals('cC', $res);

		$res = $this->Request->get_sel_from_post('z', ['zZ'], 'zZ');
		$this->assertEquals('zZ', $res);
	}


	public function testSetGetArrayFromGet(): void
	{
		$_GET['a'] = ['aa', 'bb', 'cc'];
		$_GET['b'] = ['p' => ['g' => 'G', 'h' => 'H'], 'jj', 'ii', [1, 2, 3]];
		$_GET['c'] = ['jj', 'ii'];
		$this->Request->set_get();

		$res = $this->Request->get_array_from_get('x');
		$this->assertEquals([], $res);

		$res = $this->Request->get_array_from_get('a', ['dd', 'ee']);
		$this->assertEquals(['aa', 'bb', 'cc'], $res);

		$res = $this->Request->get_array_from_get('c', ['cC']);
		$this->assertEquals(['jj', 'ii'], $res);

		$res = $this->Request->get_array_from_get('z', ['zZ']);
		$this->assertEquals(['zZ'], $res);

		$res = $this->Request->get_array_from_get('b');
		$this->assertEquals(['jj', 'ii'], $res);
	}

	public function testSetGetArrayFromPost(): void
	{
		$_POST['a'] = ['aa', 'bb', 'cc'];
		$_POST['b'] = ['p' => ['g' => 'G', 'h' => 'H'], 'jj', 'ii', [1, 2, 3]];
		$_POST['c'] = ['jj', 'ii'];
		$this->Request->set_post();

		$res = $this->Request->get_array_from_post('x');
		$this->assertEquals([], $res);

		$res = $this->Request->get_array_from_post('a', ['dd', 'ee']);
		$this->assertEquals(['aa', 'bb', 'cc'], $res);

		$res = $this->Request->get_array_from_post('c', ['cC']);
		$this->assertEquals(['jj', 'ii'], $res);

		$res = $this->Request->get_array_from_post('z', ['zZ']);
		$this->assertEquals(['zZ'], $res);

		$res = $this->Request->get_array_from_post('b');
		$this->assertEquals(['jj', 'ii'], $res);
	}


	public function testGetRequest(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['REDIRECT_URL'] = '/redirect/path';
		$this->Request->set_server();

		$res = $this->Request->get_request();
		$this->assertIsArray($res);
		$this->assertEquals(['POST', '/redirect/path'], $res);

		$res = $this->Request->get_request('DELETE');
		$this->assertIsArray($res);
		$this->assertEquals(['DELETE', '/redirect/path'], $res);

		$res = $this->Request->get_request(path: '/test');
		$this->assertIsArray($res);
		$this->assertEquals(['DELETE', '/test'], $res);

		$res = $this->Request->get_request('GET', '/path');
		$this->assertIsArray($res);
		$this->assertEquals(['GET', '/path'], $res);
	}

	public function testIsPost(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$this->Request->set_server();

		$res = $this->Request->is_post();
		$this->assertFalse($res);

		$res = $this->Request->set_method('POST');
		$res = $this->Request->is_post();
		$this->assertTrue($res);
	}

	public function testIsSSL(): void
	{
		$res = $this->Request->is_ssl();
		$this->assertFalse($res);

		$_SERVER['HTTPS'] = 'yes';
		$_SERVER['SERVER_PORT'] = 443;
		$this->Request->set_server();

		$res = $this->Request->is_ssl(no_cache: true);
		$this->assertTrue($res);

		$res = $this->Request->is_ssl(port: null, no_cache: true);
		$this->assertTrue($res);
	}

	public function testIsAjax(): void
	{
		$res = $this->Request->is_ajax();
		$this->assertFalse($res);

		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->Request->set_server();

		$res = $this->Request->is_ajax(no_cache: true);
		$this->assertTrue($res);
	}
}
