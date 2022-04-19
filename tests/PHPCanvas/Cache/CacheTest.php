<?php

use PHPCanvas\Cache\Cache;
use PHPCanvas\Cache\CacheInterface;
use PHPCanvasTestHelpersTrait as PHPCanvasTestHelpersTrait;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
	use PHPCanvasTestHelpersTrait;

	protected $Cache;
	public static $perm;
	public static $ttl;

	public static function setUpBeforeClass(): void
	{
		static::tmpdir_make(self::class);
		self::$perm = 0755;
		self::$ttl = 900;
	}

	public function setUp(): void
	{
		$this->Cache = new Cache(static::$tmpdir, self::$perm, self::$ttl);
	}

	public function testCreate()
	{
		$this->assertInstanceOf(CacheInterface::class, $this->Cache);
	}

	public function testIndex()
	{
		$index_path = $this->Cache->index('foo/bar/123', 3);
		$this->assertEquals('foo/bar/0/123', $index_path);

		$index_path = $this->Cache->index('foo/bar/abcdef', 3);
		$this->assertEquals('foo/bar/abc/abcdef', $index_path);

		$index_path = $this->Cache->index('ab', 5);
		$this->assertEquals('ab/ab', $index_path);
	}
	
	public function testPutString()
	{
		$path = 'put/test';
		$data = 'data';

		$result = $this->Cache->put($path, $data);
		$this->assertEquals(true, $result);

		$cache_data = file_get_contents(static::$tmpdir . '/' . $path);
		$this->assertEquals($data, $cache_data);
	}
	
	public function testPutWithIndex()
	{
		$path = ['put/123_test_with_index', 3];
		$data = 'data';

		$result = $this->Cache->put($path, $data);
		$this->assertEquals(true, $result);
	}
	
	public function testPutBoolean()
	{
		$path = 'put/test_bool';
		$data = false;

		$result = $this->Cache->put($path, $data);
		$this->assertEquals(true, $result);
	}
	
	public function testPutArray()
	{
		$path = 'put/test_array';
		$data = [1, 2, 'key' => 'value'];

		$result = $this->Cache->put($path, $data);
		$this->assertEquals(true, $result);
	}
	
	public function testPutObject()
	{
		$path = 'put/test_object';
		$data = new stdClass();
		$data->a = 'A';
		$data->b = ['key' => 'value'];

		$result = $this->Cache->put($path, $data);
		$this->assertEquals(true, $result);
	}
	
	public function testPermission()
	{
		$path = 'permission/test';
		$data = 'data';

		$this->Cache->put($path, $data);
		$dir_permission = fileperms(static::$tmpdir . '/' . dirname($path));

		$dir_permission_string = substr(sprintf('%o', $dir_permission), -4);
		$permission_string = substr('0' . sprintf('%o', self::$perm), -4);

		$this->assertEquals($permission_string, $dir_permission_string);
	}

	public function testGetString()
	{
		$path = 'get/string';
		$data = 'data';

		$this->Cache->put($path, $data);
		$cache_data = $this->Cache->get($path);

		$this->assertEquals($data, $cache_data);
	}
	
	public function testGetWithIndex()
	{
		$path = ['get/with_index', 3];
		$data = 'data';

		$this->Cache->put($path, $data);
		$cache_data = $this->Cache->get($path);

		$this->assertEquals($data, $cache_data);
	}
	
	public function testGetBoolean()
	{
		$path = 'get/boolean';
		$data = false;

		$this->Cache->put($path, $data);
		$cache_data = $this->Cache->get($path);

		$this->assertEquals($data, $cache_data);		
	}
	
	public function testGetArray()
	{
		$path = 'get/array';
		$data = [1, 2, 'key' => 'value'];

		$this->Cache->put($path, $data);
		$cache_data = $this->Cache->get($path);

		$this->assertEquals($data, $cache_data);
	}
	
	public function testGetObject()
	{
		$path = 'get/object';
		$data = new stdClass();
		$data->a = 'A';
		$data->b = ['key' => 'value'];

		$this->Cache->put($path, $data);
		$cache_data = $this->Cache->get($path);

		$this->assertEquals($data, $cache_data);
	}
	
	public function testGetWithTTL()
	{
		$path = 'get/ttl';
		$data = 'data';

		$this->Cache->put($path, $data);
		touch(static::$tmpdir . '/' . $path, time() - 100);
		
		$cache_data = $this->Cache->get($path, 10);
		$this->assertEquals('', $cache_data);

		$cache_data = $this->Cache->get($path, 300);
		$this->assertEquals($data, $cache_data);

		$cache_data = $this->Cache->get($path, true);
		$this->assertEquals($data, $cache_data);

		//path2 and path3 to work around file stat cache:

		$this->Cache->put("{$path}2", $data);
		touch(static::$tmpdir . '/' . "{$path}2", time() - self::$ttl + 100);
		$cache_data = $this->Cache->get($path);
		$this->assertEquals($data, $cache_data);

		$this->Cache->put("{$path}3", $data);
		touch(static::$tmpdir . '/' . "{$path}3", time() - self::$ttl - 100);
		$cache_data = $this->Cache->get("{$path}3");
		$this->assertEquals('', $cache_data);
	}
	
	public function testDelete()
	{
		$path = 'get/string';
		$data = 'data';

		$this->Cache->put($path, $data);
		$this->assertTrue(file_exists(static::$tmpdir . '/' . $path));

		$cache_data = $this->Cache->get($path);
		$this->assertEquals($data, $cache_data);

		$this->Cache->delete($path);
		$this->assertFalse(file_exists(static::$tmpdir . '/' . $path));

		$cache_data = $this->Cache->get($path);
		$this->assertEquals('', $cache_data);
	}
	
	public function testDeleteWithIndex()
	{
		$index = '123';
		$path = [$index . '_with_index', 3];
		$data = 'data';

		$this->Cache->put($path, $data);
		$this->assertTrue(file_exists(static::$tmpdir . "/$index/" . $path[0]));

		$cache_data = $this->Cache->get($path);
		$this->assertEquals($data, $cache_data);

		$this->Cache->delete($path);
		$this->assertFalse(file_exists(static::$tmpdir . "/$index/" . $path[0]));

		$cache_data = $this->Cache->get($path);
		$this->assertEquals('', $cache_data);
	}
	
	public function testTest()
	{
		$path = 'test';
		$data = 'data';

		$this->Cache->put($path, $data);
		touch(static::$tmpdir . '/' . $path, time() - 100);

		$test = $this->Cache->test($path);
		$this->assertTrue($test);

		$test = $this->Cache->test($path, 200);
		$this->assertTrue($test);

		$test = $this->Cache->test($path, 10);
		$this->assertFalse($test);

		$test = $this->Cache->test($path, true);
		$this->assertTrue($test);
	}
	
	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
	}
}