<?php

namespace PHPCanvas;

use PHPUnit\Framework\TestCase;
use PHPCanvasTestHelpersTrait;

class ConfigTest extends TestCase
{
	use PHPCanvasTestHelpersTrait;

	protected ConfigInterface $Config;
	public static string $config1;
	public static string $config2;
	public static string $serialized;

	public static function setUpBeforeClass(): void
	{
		static::tmpdir_make(self::class);

//		$config1 = [
//			'a' => 'A',
//			'b' => 'B',
//			'c' => 'C',
//			'd' => 'D',
//		];
//
//		$config2 = [
//			'E' => 'E',
//			'd' => 'D-NEW',
//		];

		foreach (['config1' => 100, 'config2' => 30] as $config_name => $n) {
			$a = $$config_name;
			foreach (range(1, $n) as $i) {
				$a['a_b_c_' . $i] = md5($i);
			}
			if ($config_name === 'config1') {
				$a['aaa'] = 'AAA';
			}
			if ($config_name === 'config2') {
				$a['BBB'] = 'bbb';
			}

			self::$$config_name = static::$tmpdir . '/' . $config_name . '.php';
			file_put_contents(self::$$config_name, '<?php return ' . var_export($a, true) . ';');
		}
	}

	public function setUp(): void
	{
		$this->Config = new Config([self::$config1, self::$config2]);
	}

	public function testCreate()
	{
		$this->assertInstanceOf(ConfigInterface::class, $this->Config);
	}

	public function testCreateWithExtraParameters()
	{
		$Config = new Config([self::$config1], ['adhoc1' => 'value1', 'adhoc2' => false]);
		$this->assertEquals('value1', $Config->adhoc1);
		$this->assertFalse($Config->adhoc2);
	}

	public function testGet()
	{
		$this->assertEquals('AAA', $this->Config->aaa);
		$this->assertEquals('bbb', $this->Config->BBB);
	}

	public function testSet()
	{
		$this->Config->b = 'B2';
		$this->assertEquals('B2', $this->Config->b);
	}

	public function testUnset()
	{
		unset($this->Config->bbb);
		$this->assertFalse(isset($this->Config->bbb));
	}

	public function testList()
	{
		$config = $this->Config->list();
		$this->assertIsArray($config);
		$this->assertCount(102, $config);
	}

	public function testSerializable()
	{
		// Config is typically cached via Container class

		$serialized = serialize($this->Config);
		$this->assertNotFalse($serialized);
	}

	public function testUnSerializable()
	{
		$value = $this->Config->a;
		$serialized = serialize($this->Config);
		$Config2 = unserialize($serialized);
		$this->assertInstanceOf(ConfigInterface::class, $Config2);
		$this->assertTrue($Config2->a === $value);
	}

	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
	}
}