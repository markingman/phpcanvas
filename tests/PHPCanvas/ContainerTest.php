<?php

use PHPCanvas\Container;
use PHPCanvas\ContainerInterface;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
	use PHPCanvasTestHelpersTrait;

	protected $Container;
	public static $dir_classes;
	public static $dir_locations;

	public static function mock_autoload($class): void
	{
		include_once static::$dir_classes . '/' . $class . '.php';
	}

	/*public static function get_closure_for_TestClass1()
	{
		self::mock_autoload(TestClass1);

		return new TestClass1;
	}*/

	public static function setUpBeforeClass(): void
	{
		$class = self::class;

#		static::tmpdir_remove($class);
		static::tmpdir_make($class);
		static::$dir_classes = static::$tmpdir . '/classes';
		mkdir(static::$dir_classes);

		static::$dir_locations = static::$tmpdir . '/locations';
		mkdir(static::$dir_locations);

		//Plain simple class

		$class1 = 'TestClass1';
		file_put_contents(
			static::$dir_classes . "/$class1.php",
			<<<__
<?php
class $class1
{
	public \$A = 'A';
}
__
		);

		file_put_contents(static::$dir_locations . "/register.$class1.php", <<<__
<?php
return function (\$Container) {
	{$class}::mock_autoload('$class1');

	return new $class1;
};
__
		);

		//Class with arguments in constructor

		$class2 = 'TestClass2';
		file_put_contents(
			static::$dir_classes . "/$class2.php",
			<<<__
<?php
class $class2
{
	public \$a;
	public \$b;

	public function __construct(\$a/* = 1*/, \$b/* = 2*/)
	{
		\$this->a = \$a;
		\$this->b = \$b;
	}
}
__
		);

		file_put_contents(static::$dir_locations . "/register.$class2.php", <<<__
<?php
return function (\$Container, \$a = 1, \$b = 2) {
	{$class}::mock_autoload('$class2');

	return new $class2(\$a, \$b);
};
__
		);

		//Class with dependency

		$class3 = 'TestClass3';
		file_put_contents(
			static::$dir_classes . "/$class3.php",
			<<<__
<?php
use TestClass2 as TestClass2;

class $class3
{
	public \$TestClass2;

	public function __construct(TestClass2 \$TestClass2)
	{
		\$this->TestClass2 = \$TestClass2;
	}
}
__
		);

		file_put_contents(static::$dir_locations . "/register.$class3.php", <<<__
<?php
return function (\$Container) {
	{$class}::mock_autoload('$class3');

	return new $class3(\$Container['$class2']);
};
__
		);
	}

	public function setUp(): void
	{
		$this->Container = new Container();
	}

	public function testCreate()
	{
		$this->assertInstanceOf(ContainerInterface::class, $this->Container);
	}

	public function testStore()
	{
		$store = static::$tmpdir;
		$this->Container->set_store($store);
		
		$store_from_container = $this->Container->get_store();
		$store_expected = $store;
		
		$this->assertEquals($store_expected, $store_from_container);
	}

	public function testRegister()
	{
		$class = 'TestClass1';
		$closure = function() use ($class) {
			self::mock_autoload($class);

			return new $class;
		};

		$this->Container->register($class, $closure);

		$from_container = $this->Container->list_registry();
		$expected = [$class => $closure];

		$this->assertEquals($expected, $from_container);
	}

	public function testRegisterIfNotExists()
	{
		$class = 'TestClass1';
		$closure = function() use ($class) {
			self::mock_autoload($class);

			return new $class;
		};

		$class2 = 'TestClass2';
		$closure2 = function() use ($class2) {
			self::mock_autoload($class2);

			return new $class2;
		};

		$this->Container->register($class, $closure);
		$this->Container->register_if_not_exists($class, $closure2);
		$this->Container->register_if_not_exists($class2, $closure2);

		$from_container = $this->Container->list_registry();
		$expected = [$class => $closure, $class2 => $closure2];
		
		$this->assertEquals($expected, $from_container);
	}

	public function testLocate()
	{
		$class = 'TestClass1';
		$path = static::$dir_locations . "/register.$class.php";

		$this->Container->locate($class, $path);
		
		$from_container = $this->Container->list_locations();
		$expected = [$class => $path];
		
		$this->assertEquals($expected, $from_container);
	}

	public function testLocateWithArguments()
	{
		//public function lcate($name, $path, $args = null)

		$class = 'TestClass1';
		$path = static::$dir_locations . "/register.$class.php";
		$a = 123;
		$b = 'test';
		$args = ['a' => $a, 'b' => $b];

		$this->Container->locate($class, $path, $args);
		
		$from_container = $this->Container->list_locations();
		$expected = [$class => [$path, $args]];
		
		$this->assertEquals($from_container, $expected);
	}
	
	public function testLocateIfNotExists()
	{
		$class = 'TestClass1';
		$path = static::$dir_locations . "/register.$class.php";

		$class2 = 'TestClass2';
		$path2 = static::$dir_locations . "/$class2.php";

		$this->Container->locate($class, $path);
		$this->Container->locate_if_not_exists($class, $path2);
		$this->Container->locate_if_not_exists($class2, $path2);

		$from_container = $this->Container->list_locations();
		$expected = [$class => $path, $class2 => $path2];
		
		$this->assertEquals($from_container, $expected);
	}

	public function testLocates()
	{
		$locates = [
			['TestClass1', static::$dir_locations . '/register.TestClass1.php'],
			['TestClass2', static::$dir_locations . '/register.TestClass2.php', ['a']]
		];

		$this->Container->locates($locates);
		
		$from_container = $this->Container->list_locations();
		$expected = [
			'TestClass1' => static::$dir_locations . '/register.TestClass1.php',
			'TestClass2' => [
				static::$dir_locations . '/register.TestClass2.php',
				['a']
			]
		];
		
		$this->assertEquals($expected, $from_container);
	}

	public function testCreateFromLocation()
	{
		$class = 'TestClass1';
		$path = static::$dir_locations . "/register.$class.php";

		$this->Container->locate($class, $path);
		
		$TestClass1 = $this->Container->create($class);

		$this->assertInstanceOf($class, $TestClass1);
	}

	public function testCreateFromRegistry()
	{
		$class = 'TestClass1';
		$closure = function() use ($class) {
			self::mock_autoload($class);

			return new $class;
		};

		$this->Container->register($class, $closure);

		$TestClass1 = $this->Container->create($class);

		$this->assertInstanceOf($class, $TestClass1);
	}

	//testCreateWhenInstanciasted

	//testCreateWhenRegisteredAndLocated
	//testCreateWhenRegisteredAndLocatedAndInstanciasted

	//testCreateWithArgs

	//testCallWithArgs

	//testCreateWithInjection
	//testCreateWithInjectionAndArgs

	//testCreateWithInheritedInjection
	
	//testReflection

	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
	}
}