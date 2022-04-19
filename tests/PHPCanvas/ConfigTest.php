<?php

use PHPCanvas\Config;
use PHPCanvas\ConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
	use PHPCanvasTestHelpersTrait;

	protected $Config;
	public static $config1;
	public static $config2;
	public static $serialized;

	public static function setUpBeforeClass(): void
	{
		static::tmpdir_make(self::class);

		$config1 = array(
			'a' => 'A',
			'b' => 'B',
			'c' => 'C',
			'd' => 'D',
		);
		
		$config2 = array(
			'E' => 'E',
			'd' => 'D-NEW',
		);
		
		foreach(array('config1' => 100, 'config2' => 30) as $config_name => $n) {
			$a = $$config_name;
			foreach (range(1, $n) as $i) {
				$a['a_b_c_' . $i] = md5(rand(100000, 999999));
			}
			self::$$config_name = static::$tmpdir . '/' . $config_name. '.php';
			file_put_contents(self::$$config_name, '<?php return ' . var_export($a, true) . ';');
		}
	}

	public function setUp(): void
	{
		$this->Config = new Config(array(self::$config1, self::$config2));
	}

	public function testCreate()
	{
		$this->assertInstanceOf(ConfigInterface::class, $this->Config);
	}

	public function testCreateWithExtraParameters()
	{
		$Config = new Config(array(self::$config1), array('adhoc1' => 'value1', 'adhoc2' => false));
		$this->assertTrue($Config->adhoc1 === 'value1');
	}
	
	public function testGet()
	{
		$this->assertTrue($this->Config->a === 'A');
	}

	public function testSet()
	{
		$this->Config->b = 'B2';
		$this->assertTrue($this->Config->b === 'B2');
	}

	public function testUnset()
	{
		unset($this->Config->b);
		$this->assertTrue(!isset($this->Config->b));
	}

	public function testList()
	{
		$config = $this->Config->list();
		$this->assertIsArray($config);
	}

	public function testSerializable()
	{
		// Config is typically cached via Container class

		$serialized = serialize($this->Config);
		$this->assertTrue($serialized !== false);
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