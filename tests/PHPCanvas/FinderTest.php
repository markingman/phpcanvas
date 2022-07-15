<?php

use PHPCanvas\Finder;
use PHPCanvas\FinderInterface;
use PHPUnit\Framework\TestCase;

class FinderTest extends TestCase
{
	use PHPCanvasTestHelpersTrait;

	protected $Finder;
// 	public static $dir1 = '/sample/dir1';
// 	public static $dir1_name = 'test/dir1';
// 	public static $dir2 = '/sample/dir2';
// 	public static $dir2_name = 'test/dir2';
// 	public static $dir3 = '/dir3';
// 	public static $dir3_name =  = 'dir3';
	public static $dirs;
	public static $serialized;

	public static function setUpBeforeClass(): void
	{
// 		static::tmpdir_remove();

		static::tmpdir_make(md5(self::class));//md5() as OSX didn't like "FinderTest"??

		static::$dirs = [];

		foreach ([
					 'test/dir1' => '/sample/dir1',
					 'test/dir2' => '/sample/dir2',
					 'xyz/dir3' => '/dir3',
				 ] as $name => $path) {
			$dir = static::$tmpdir . $path;
			if (!is_dir($dir)) {
				mkdir($dir, 0755, true);
			}
			$file = ($name === 'xyz/dir3') ? 'xyz' : 'test';
			file_put_contents($dir . '/test', $file);
			static::$dirs[$name] = realpath($dir);
		}
	}

	public function setUp(): void
	{
		$this->Finder = new Finder(static::$dirs);
	}

	public function testCreate()
	{
		$this->assertInstanceOf(FinderInterface::class, $this->Finder);
	}

	public function testSetDirs()
	{
		$result = $this->Finder->set_dirs(static::$dirs);
		$this->assertTrue($result);
	}

	public function testGetDir()
	{
		$dir = 'test/dir1';
		$result = $this->Finder->get_dir($dir);
		$this->assertEquals($result, static::$dirs[$dir]);
	}

	public function testGetDirs()
	{
		$result = $this->Finder->get_dirs();
		$this->assertEquals($result, static::$dirs);
	}

	public function testGet()
	{
		$res = $this->Finder->get('test');
		$this->assertIsReadable($res);

		$res2 = file_get_contents($res);
		$this->assertEquals($res2, 'test');
	}

	public function testGetNotFound()
	{
		$res = $this->Finder->get('testx');
		$this->assertNull($res);
	}

	public function testGetCache()
	{
		foreach (range(1, 3) as $i) {
			$res = $this->Finder->get('test', null, true);
			$this->assertIsReadable($res);

			$res2 = file_get_contents($res);
			$this->assertEquals($res2, 'test');
		}
	}

	public function testGetNameDir()
	{
		$res = $this->Finder->get('test', 'xyz/dir3');
		$this->assertNotNull($res);
		$this->assertIsReadable($res);

		$res2 = file_get_contents($res);
		$this->assertEquals($res2, 'xyz');
	}

	public function testGetNameDirCache()
	{
		foreach (range(1, 3) as $i) {
			$res = $this->Finder->get('test', 'xyz/dir3', true);
			$this->assertIsReadable($res);

			$res2 = file_get_contents($res);
			$this->assertEquals($res2, 'xyz');
		}
	}

	public function testGlob()
	{
		$res = $this->Finder->glob('*');
		foreach ($res as $k => $v) {
			$this->assertTrue($k === 'test');
			$this->assertIsReadable($v);
		}
	}

	public function testGlobDir()
	{
		$res = $this->Finder->glob('*', 'xyz/dir3');
		foreach ($res as $k => $v) {
			$this->assertTrue($k === 'test');
			$this->assertIsReadable($v);
		}
	}

	public function testGlobDirs()
	{
		$dirs = ['test/dir2', 'xyz/dir3'];
		$res = $this->Finder->glob('*', $dirs);
		foreach ($res as $k => $v) {
			$this->assertTrue($k === 'test');
			$this->assertIsReadable($v);
		}
	}

	public function testSerializable()
	{
		// Finder is typically cached via Container class

		$serialized = serialize($this->Finder);
		$this->assertTrue($serialized !== false);
	}

	public function testUnSerializable()
	{
		$dir = 'test/dir1';
		$result = $this->Finder->get_dir($dir);
		$serialized = serialize($this->Finder);
		$Finder2 = unserialize($serialized);
		$this->assertInstanceOf(FinderInterface::class, $Finder2);
		$this->assertTrue($Finder2->get_dir($dir) === $result);
	}

	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
	}
}