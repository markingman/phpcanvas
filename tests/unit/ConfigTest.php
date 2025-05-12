<?php declare(strict_types=1);

namespace PHPCanvas;

use PHPCanvas\Exception\ConfigUnexpectedValueException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConfigTest extends TestCase
{
	use TestHelpersTrait;

	public static string $config1;
	public static string $config2;
	public static string $serialized;
	protected ConfigInterface $Config;

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
				$configs[$name]['a_b_c_' . $i] = md5((string)$i);
			}

			self::$$name = static::$tmpdir . '/' . $name . '.php';
			if (!file_put_contents(self::$$name, '<?php return ' . var_export($configs[$name], true) . ';')) {
				throw new RuntimeException("Could not write ConfigTest fixture file '$name'");
			}
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

	public function testCreate(): void
	{
		$this->assertInstanceOf(ConfigInterface::class, $this->Config);
	}

// 	public function testCreateEmpty()
// 	{
// 		$this->assertInstanceOf(ConfigInterface::class, new Config());
// 	}

	public function testCreateWithExtraParameters(): void
	{
		$Config = new Config([self::$config1], ['adhoc1' => 'value1', 'adhoc2' => 'value2']);
		$this->assertSame('value1', $Config->adhoc1);
		$this->assertSame('value2', $Config->adhoc2);
	}

	public function testGet(): void
	{
		$this->assertSame('A', $this->Config->a);
		$this->assertSame('B', $this->Config->b);
	}

	public function testSet(): void
	{
		$this->Config->b = 'B2';
		$this->assertSame('B2', $this->Config->b);
	}

	public function testUnset(): void
	{
		unset($this->Config->bbb);
		$this->assertFalse(isset($this->Config->bbb));
	}

	public function testList(): void
	{
		$config = $this->Config->list();
		$this->assertIsArray($config);
		$this->assertCount(105, $config);
	}

	public function testSerializable(): void
	{
		$serialized = serialize($this->Config);
		$this->assertNotFalse($serialized);
	}

	public function testUnserializable(): void
	{
		$value = $this->Config->a;
		$serialized = serialize($this->Config);
		$Config2 = unserialize($serialized);
		$this->assertInstanceOf(ConfigInterface::class, $Config2);
		$this->assertSame($Config2->a, $value);
	}

	public function testUnserializeEmpty(): void
	{
		$this->expectException(ConfigUnexpectedValueException::class);
		$this->expectExceptionMessage('Config requires a "config" array');

		$config = new Config();

		try {
			$config->__unserialize([]);
		} catch (ConfigUnexpectedValueException $e) {
// 			$this->assertSame(ConfigError::CALL_ERR, $e->getErrorCode());
			throw $e;
		}
	}

	public function testUnserializeNotArray(): void
	{
		$this->expectException(ConfigUnexpectedValueException::class);
		$this->expectExceptionMessage('Config requires a "config" array');

		$config = new Config();

		$config->__unserialize(['config' => 'string']);
	}

	public function testUnserializeKeyNotString(): void
	{
		$this->expectException(ConfigUnexpectedValueException::class);
		$this->expectExceptionMessage('Tried to load non-string Config key');

		$config = new Config();

		$config->__unserialize([
			'config' => [
				0 => 'value',
			]
		]);
	}

	public function testUnserializeValueNotString(): void
	{
		$this->expectException(ConfigUnexpectedValueException::class);
		$this->expectExceptionMessage('Tried to load non-string Config value for key \'key\'');

		$config = new Config();

		$config->__unserialize([
			'config' => [
				'key' => 100,
			]
		]);
	}
}
