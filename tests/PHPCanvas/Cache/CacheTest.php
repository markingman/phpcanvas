<?php

use PHPCanvas\Cache\Cache;
use PHPCanvas\Cache\CacheInterface;
use PHPCanvasTestHelpersTrait as PHPCanvasTestHelpersTrait;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator as RecursiveIteratorIterator;
use RecursiveDirectoryIterator as RecursiveDirectoryIterator;

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
		$path = ['get/123with_index', 3];
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
		touch(static::$tmpdir . '/' . $path, time() - 1);
		clearstatcache();
		$cache_data = $this->Cache->get($path);
		$this->assertEquals('', $cache_data);

		$this->Cache->put($path, $data, 100);
		touch(static::$tmpdir . '/' . $path, time() - 101);
		clearstatcache();
		$cache_data = $this->Cache->get($path);
		$this->assertEquals('', $cache_data);

		$this->Cache->put($path, $data, 1000);
		$cache_data = $this->Cache->get($path, 1001);
		$this->assertEquals('', $cache_data);
		$cache_data = $this->Cache->get($path, 500);
		$this->assertEquals($data, $cache_data);

		$this->Cache->put($path, $data, 100);
		$cache_data = $this->Cache->get($path);
		$this->assertEquals($data, $cache_data);
		touch(static::$tmpdir . '/' . $path, time() - 1);
		clearstatcache();
		$cache_data = $this->Cache->get($path);
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

		$this->Cache->put($path, $data, 300);

		$test = $this->Cache->test($path);
		$this->assertTrue($test);

		$test = $this->Cache->test($path, 200);
		$this->assertTrue($test);

		$test = $this->Cache->test($path, 400);
		$this->assertFalse($test);

		touch(static::$tmpdir . '/' . $path, time() + 50);
		clearstatcache();
		$test = $this->Cache->test($path, 100);
		$this->assertFalse($test);
	}
	
	public function testGC()
	{
		$this->Cache->gc(10000);
		$this->assertEquals([], $this->listFiles(static::$tmpdir));

		$path1 = 'test1';
		$data1 = 'data1';
		$this->Cache->put($path1, $data1, 100);

		$path2 = 'test2';
		$data2 = 'data2';
		$this->Cache->put($path2, $data2, 200);

		$this->Cache->gc(101);
		$this->assertEquals([static::$tmpdir . '/' . $path2], $this->listFiles(static::$tmpdir));
	}

	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
	}

	protected function listFiles($dir): array
	{
		$files = [];

		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
		foreach ($it as $f) {
			if (!$it->isDir()) {
				$files[] = $f->getPathname();
			}
		}

		return $files;
	}
}