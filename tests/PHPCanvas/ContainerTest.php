<?php // $Id: ContainerTest.php 772 2018-05-17 09:12:20Z dev $

use PHPCanvasTestHelpersTrait as PHPCanvasTestHelpersTrait;
use PHPCanvas\Container;
use PHPCanvas\ContainerInterface;

class ContainerTest extends PHPUnit_Framework_TestCase
{
	use PHPCanvasTestHelpersTrait;

	protected $Container;
	public static $dir_classes;
	public static $dir_locations;

	public static function mock_autoload($class)
	{
		include_once static::$dir_classes . '/' . $class . '.php';
	}

	/*public static function get_closure_for_TestClass1()
	{
		self::mock_autoload(TestClass1);

		return new TestClass1;
	}*/

	public static function setUpBeforeClass()
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

	public function setUp()
	{
		$this->Container = new Container();
	}

	public function testCreate()
	{
		$this->assertInstanceOf(ContainerInterface::class, $this->Container);
	}

	public function testStore()
	{
		//public function set_store($path)
		//public function get_store()

		$store = static::$tmpdir;
		$this->Container->set_store($store);
		
		$store_from_container = $this->Container->get_store();
		$store_expected = $store;
		
		$this->assertEquals($store_expected, $store_from_container);
	}
	
	public function testLocate()
	{
		//public function locate($name, $path[, $args = null])

		$class = 'TestClass1';
		$path = static::$dir_locations . "/$class.php";

		$this->Container->locate($class, $path);
		
		$locations_from_container = $this->Container->list_locations();
		$locations_expected = [$class => $path];
		
		$this->assertEquals($locations_expected, $locations_from_container);
	}

	public function testLocateWithArguments()
	{
		//public function lcate($name, $path, $args = null)

		$class = 'TestClass1';
		$path = static::$dir_classes . "/$class.php";
		$a = 123;
		$b = 'test';
		$args = ['a' => $a, 'b' => $b];

		$this->Container->locate($class, $path, $args);
		
		$locations_from_container = $this->Container->list_locations();
		$locations_expected = [$class => [$path, $args]];
		
		$this->assertArrayHasKey($class, $locations_from_container);
		$this->assertEquals($locations_from_container[$class], $locations_expected[$class]);
	}
	
	
	public function testLocateIfNotExists()
	{
		//public function locate_if_not_exists($name, $path[, $args = null])

		$class_name = 'TestClass1';
		$path = static::$dir_locations . "/$class_name.php";

		$this->Container->locate($class_name, $path);
		$this->Container->locate_if_not_exists($class_name, $path);

		$class_name2 = 'TestClass2';
		$path2 = static::$dir_locations . "/$class_name2.php";

		$this->Container->locate_if_not_exists($class_name2, $path2);

		$locations_from_container = $this->Container->list_locations();
		$locations_expected = [$class_name => $path, $class_name2 => $path2];
		
		$this->assertArrayHasKey($class_name, $locations_from_container);
		$this->assertEquals($locations_from_container[$class_name], $locations_expected[$class_name]);

		$this->assertArrayHasKey($class_name2, $locations_from_container);
		$this->assertEquals($locations_from_container[$class_name2], $locations_expected[$class_name2]);
	}
	
	public function testCreateFromLocation()
	{
		$class_name = 'TestClass1';
		$path = static::$dir_classes . "/$class_name.php";

		$this->Container->locate($class_name, $path);
		
		$locations_from_container = $this->Container->list_locations();
		$locations_expected = [$class_name => $path];
		
		$this->assertEquals($locations_expected, $locations_from_container);

		$TestClass1 = $this->Container->create($class_name);

		$this->assertInstanceOf($class_name, $TestClass1);
	}

	public function testRegister()
	{
		//public function register($name, \Closure $closure)

		$class_name = 'TestClass1';
		$this->Container->register($class_name, function() use ($class_name) {
			self::mock_autoload($class_name);

			return new $class_name;
		});

		$registrations_from_container = $this->Container->list_registry();

		$this->assertArrayHasKey($class_name, $registrations_from_container);
		
		$A = $registrations_from_container[$class_name]();
		$this->assertInstanceOf($class_name, $A);
	}

	public function testRegisterFromFile()
	{
		$class_name = 'TestClass1';
		$path = static::$dir_locations . "/register.$class_name.php";

		$this->Container->register($class_name, include $path);

		$registrations_from_container = $this->Container->list_registry();

		$this->assertArrayHasKey($class_name, $registrations_from_container);
		
		$A = $registrations_from_container[$class_name]();
		$this->assertInstanceOf($class_name, $A);
	}

	//public function register_if_not_exists($name, \Closure $closure)

	public function testCreateFromRegistry()
	{
		$class_name = 'TestClass1';
		$this->Container->register($class_name, function() use ($class_name) {
			self::mock_autoload($class_name);

			return new $class_name;
		});

		$TestClass1 = $this->Container->create($class_name);

		$this->assertInstanceOf($class_name, $TestClass1);
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

	public static function tearDownAfterClass()
	{
		static::tmpdir_remove();
	}
}