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
		static::tmpdir_make(md5(self::class));//md5() as OSX didn't like "FinderTest"??

		static::$dirs = [];
		
		foreach ([
			'test/dir1' => '/sample/dir1',
			'test/dir2' => '/sample/dir2',
			'xyz/dir3' => '/dir3',
		] as $name => $path) {
			$dir = static::$tmpdir . $path;
			mkdir($dir, 0755, true);
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

// 	public function testGet()
// 	{
//
// 	}

// 	public function testGetScopeOneDir()
// 	{
//
// 	}

// 	public function testGetScopeArrayDirs()
// 	{
//
// 	}

// 	public function testGlob()
// 	{
//
// 	}

// 	public function testGlobScopeOneDir()
// 	{
//
// 	}

// 	public function testGlobScopeArrayDirs()
// 	{
//
// 	}

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