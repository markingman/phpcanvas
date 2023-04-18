<?php

namespace PHPCanvas\Cache;

use PHPCanvasTestHelpersTrait;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use stdClass;

class CacheTest extends TestCase
{
	use PHPCanvasTestHelpersTrait;

	protected CacheInterface $Cache;
	public static int $perm;
	public static int $ttl;

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
		$res = $this->Cache->index('foo/bar/123', 3);
		$this->assertEquals('foo/bar/0/123', $res);

		$res = $this->Cache->index('foo/bar/abcdef', 3);
		$this->assertEquals('foo/bar/abc/abcdef', $res);

		$res = $this->Cache->index('ab', 5);
		$this->assertEquals('ab/ab', $res);
	}

	public function testPutString()
	{
		$path = 'put/test';

		$res = $this->Cache->put($path, 'data');
		$this->assertTrue($res);

		$res = file_get_contents(static::$tmpdir . '/' . $path);
		$this->assertEquals('data', $res);
	}

	public function testPutWithIndex()
	{
		$path = ['put/123_test_with_index', 3];

		$res = $this->Cache->put($path, 'data');
		$this->assertTrue($res);
	}

	public function testPutBoolean()
	{
		$path = 'put/test_bool';

		$res = $this->Cache->put($path, false);
		$this->assertTrue($res);
	}

	public function testPutArray()
	{
		$path = 'put/test_array';
		$data = [1, 2, 'key' => 'value'];

		$res = $this->Cache->put($path, $data);
		$this->assertEquals(true, $res);
	}

	public function testPutObject()
	{
		$path = 'put/test_object';
		$data = new stdClass();
		$data->a = 'A';
		$data->b = ['key' => 'value'];

		$res = $this->Cache->put($path, $data);
		$this->assertTrue($res);
	}

	public function testPermission()
	{
		$path = 'permission/test';
		$data = 'data';

		$this->Cache->put($path, $data);
		$dir_permission = fileperms(static::$tmpdir . '/' . dirname($path));

		$dir_permission_str = substr(sprintf('%o', $dir_permission), -4);
		$permission_str = substr('0' . sprintf('%o', self::$perm), -4);

		$this->assertEquals($permission_str, $dir_permission_str);
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
		$res = $this->Cache->get($path);

		$this->assertEquals($data, $res);
	}

	public function testGetBoolean()
	{
		$path = 'get/boolean';

		$this->Cache->put($path, false);
		$res = $this->Cache->get($path);

		$this->assertFalse($res);
	}

	public function testGetArray()
	{
		$path = 'get/array';
		$data = [1, 2, 'key' => 'value'];

		$this->Cache->put($path, $data);
		$res = $this->Cache->get($path);

		$this->assertEquals($data, $res);
	}

	public function testGetObject()
	{
		$path = 'get/object';
		$data = new stdClass();
		$data->a = 'A';
		$data->b = ['key' => 'value'];

		$this->Cache->put($path, $data);
		$res = $this->Cache->get($path);

		$this->assertEquals($data, $res);
	}

	public function testGetWithTTL()
	{
		$path = 'get/ttl';
		$data = 'data';

		$this->Cache->put($path, $data);
		touch(static::$tmpdir . '/' . $path, time() - 1);
		clearstatcache();
		$res = $this->Cache->get($path);
		$this->assertEquals('', $res);

		$this->Cache->put($path, $data, 100);
		touch(static::$tmpdir . '/' . $path, time() - 101);
		clearstatcache();

		$res = $this->Cache->get($path);
		$this->assertEquals('', $res);

		$this->Cache->put($path, $data, 1000);
		$res = $this->Cache->get($path, 1001);
		$this->assertEquals('', $res);

		$res = $this->Cache->get($path, 500);
		$this->assertEquals($data, $res);

		$this->Cache->put($path, $data, 100);
		$res = $this->Cache->get($path);
		$this->assertEquals($data, $res);

		touch(static::$tmpdir . '/' . $path, time() - 1);
		clearstatcache();

		$res = $this->Cache->get($path);
		$this->assertEquals('', $res);
	}

	public function testDelete()
	{
		$path = 'get/string';
		$data = 'data';

		$this->Cache->put($path, $data);
		$this->assertTrue(file_exists(static::$tmpdir . '/' . $path));

		$res = $this->Cache->get($path);
		$this->assertEquals($data, $res);

		$this->Cache->delete($path);
		$this->assertFalse(file_exists(static::$tmpdir . '/' . $path));

		$res = $this->Cache->get($path);
		$this->assertEquals('', $res);
	}

	public function testDeleteWithIndex()
	{
		$index = '123';
		$path = [$index . '_with_index', 3];
		$data = 'data';

		$this->Cache->put($path, $data);
		$this->assertTrue(file_exists(static::$tmpdir . "/$index/" . $path[0]));

		$res = $this->Cache->get($path);
		$this->assertEquals($data, $res);

		$this->Cache->delete($path);
		$this->assertFalse(file_exists(static::$tmpdir . "/$index/" . $path[0]));

		$res = $this->Cache->get($path);
		$this->assertEquals('', $res);
	}

	public function testTest()
	{
		$path = 'test';
		$data = 'data';

		$this->Cache->put($path, $data, 300);

		$res = $this->Cache->test($path);
		$this->assertTrue($res);

		$res = $this->Cache->test($path, 200);
		$this->assertTrue($res);

		$res = $this->Cache->test($path, 400);
		$this->assertFalse($res);

		touch(static::$tmpdir . '/' . $path, time() + 50);
		clearstatcache();
		$res = $this->Cache->test($path, 100);
		$this->assertFalse($res);
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
			if (!$it->current()->isDir()) {
				$files[] = $f->getPathname();
			}
		}

		return $files;
	}
}