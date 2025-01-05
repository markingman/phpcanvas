<?php

namespace PHPCanvas;

use PHPUnit\Framework\TestCase;

#[CoversClass(Config::class)]
class ConfigTest extends TestCase
{
	use TestHelpersTrait;

	protected ConfigInterface $Config;
	public static string $config1;
	public static string $config2;
	public static string $serialized;

	public static function setUpBeforeClass(): void
	{
		static::tmpdir_make();

		$configs = [
			'config1' => [
				'a' => 'A',
				'b' => 'B',
				'c' => 'C',
				'd' => 'D',
			],
			'config2' => [
				'E' => 'E',
				'd' => 'D-NEW',
			]
		];

		foreach (['config1' => 100, 'config2' => 30] as $name => $n) {
			foreach (range(1, $n) as $i) {
				$configs[$name]['a_b_c_' . $i] = md5($i);
			}

			self::$$name = static::$tmpdir . '/' . $name . '.php';
			file_put_contents(self::$$name, '<?php return ' . var_export($configs[$name], true) . ';');
		}
	}

	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
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
		$Config = new Config([self::$config1], ['adhoc1' => 'value1', 'adhoc2' => 'value2']);
		$this->assertEquals('value1', $Config->adhoc1);
		$this->assertEquals('value2', $Config->adhoc2);
	}

	public function testGet()
	{
		$this->assertEquals('A', $this->Config->a);
		$this->assertEquals('B', $this->Config->b);
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
		$this->assertCount(105, $config);
	}

	public function testSerializable()
	{
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
}
