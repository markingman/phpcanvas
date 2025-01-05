<?php

namespace PHPCanvas;

use PHPUnit\Framework\TestCase;
use Exception;
use Closure;

class ContainerTest extends TestCase
{
	use TestHelpersTrait;

	protected ContainerInterface $Container;
	public static string $dir_classes;
	public static string $dir_locations;
	public static string $dir_store;

	public static function mockAutoload($class): void
	{
		include_once static::$dir_classes . '/' . $class . '.php';
	}

	public static function setUpBeforeClass(): void
	{
		static::tmpdir_make();

		static::$dir_classes = static::$tmpdir . '/classes';
		mkdir(static::$dir_classes);

		static::$dir_locations = static::$tmpdir . '/locations';
		mkdir(static::$dir_locations);

		static::$dir_store = static::$tmpdir . '/cache';
		mkdir(static::$dir_store);
	}

	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
	}

	public function setUp(): void
	{
		$this->Container = new Container();
	}

	public function testCreate(): void
	{
		$this->assertInstanceOf(ContainerInterface::class, $this->Container);
	}

	public function testStore(): void
	{
		$store = static::$tmpdir;
		$this->Container->set_store($store);

		$res = $this->Container->get_store();

		$this->assertEquals(static::$tmpdir, $res, 'Can set and get the store path');
	}

	public function testLocatePath(): void
	{
		$store = realpath(static::$dir_locations);
		$this->Container->set_locate_path($store);

		$res = $this->Container->get_locate_path();

		$this->assertEquals($store, $res, 'Can set and get the locations path');
	}

	public function testAlias(): void
	{
		$this->Container->set_alias('Class1', 'Class2');

		$res = $this->Container->get_alias('Class1');

		$this->assertEquals('Class2', $res);
	}

	public function testLocate(): void
	{
		$class = 'TestClassLocate';
		$this->createClassPlainSimple($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);

		$res = $this->Container->list_locations();
		$exp = [$class => [$path]];

		$this->assertEquals($exp, $res);
	}

	public function testLocateWithArguments(): void
	{
		$class = 'TestClassLocateWithArguments';
		$this->createClassPlainSimple($class);
		$path = $this->getClassLocationPath($class);

		$args = ['a' => 123, 'b' => 'test'];
		$this->Container->locate($class, $path, $args);

		$res = $this->Container->list_locations();
		$exp = [$class => [$path, $args]];

		$this->assertEquals($exp, $res);
	}

	public function testRegister(): void
	{
		$class = 'TestClassRegister';
		$this->createClassPlainSimple($class, true);
		$closure = $this->getClassClosure($class);

		$this->Container->register($class, $closure);

		$res = $this->Container->list_registry();
		$exp = [$class => $closure];

		$this->assertEquals($exp, $res, 'Can register a simple class');
	}

	public function testCreateFromLocation()
	{
		// create from explicitly set location path

		$class = 'TestClassCreateFromLocation';
		$this->createClassPlainSimple($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$res = $this->Container->create($class);

		$this->assertInstanceOf($class, $res);
	}

	public function testCreateFromAssumedLocation()
	{
		// no location path set, assume closure is saved in locate_path

		$this->Container->set_locate_path(realpath(static::$dir_locations));

		$class = 'TestClassCreateFromAssumedLocation';
		$this->createClassPlainSimple($class, true);

		$res = $this->Container->create($class);

		$this->assertInstanceOf($class, $res);
	}

	public function testCreateFromLocationParseFailure()
	{
		// calling closure throws an exception

		$this->Container->set_locate_path(realpath(static::$dir_locations));

		$class = 'TestClassCreateParseFailure';
		$this->createClassFailure($class, 1);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('CONTAINER_LOAD_FAILURE; Could not create TestClassCreateParseFailure, syntax error, unexpected identifier "FAILURE"');

		$this->Container->create($class);
	}
		
	public function testCreateFromLocationExceptionFailure()
	{
		// calling closure throws an exception

		$this->Container->set_locate_path(realpath(static::$dir_locations));

		$class = 'TestClassCreateExceptionFailure';
		$this->createClassFailure($class, 2);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('CONTAINER_LOAD_FAILURE; Could not create TestClassCreateExceptionFailure, Exception thrown');

		$this->Container->create($class);
	}
		
	public function testCreateFromLocationReturnFailure()
	{
		$this->Container->set_locate_path(realpath(static::$dir_locations));

		$class = 'TestClassCreateClosureFailure';
		$this->createClassFailure($class, 3);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('CONTAINER_TYPE_FAILURE; Could not create TestClassCreateClosureFailure, expected Closure not found');

		$this->Container->create($class);
	}

	public function testCreateFromRegistry()
	{
		$class = 'TestClassCreateFromRegistry';
		$this->createClassPlainSimple($class, true);
		$closure = $this->getClassClosure($class);

		$this->Container->register($class, $closure);
		$res = $this->Container->create($class);

		$this->assertInstanceOf($class, $res);
	}

	public function testCreateFromRegistryFailure()
	{
		$class = 'TestClassCreateFromRegistryFailure';
		$this->createClassPlainSimple($class, false);

		$this->Container->register($class, function() {
			throw new Exception('- exception message -');
		});

		$this->expectException(Exception::class);
		$this->expectExceptionMessage("CONTAINER_CREATE_ERR; could not create '$class', - exception message -");
		
		$res = $this->Container->create($class);
	}

	public function testCreateFromRegistryNotObjectFailure()
	{
		$class = 'TestClassCreateFromRegistryNotObjectFailure()';
		$this->createClassPlainSimple($class, false);

		$this->Container->register($class, function() {
			return null;
		});

		$this->expectException(Exception::class);
		$this->expectExceptionMessage("CONTAINER_OBJECT_ERR; object not created for '$class'");
		
		$res = $this->Container->create($class);
	}

	public function testCreateFromStoredInstantiate()
	{
		$class = 'TestClassCreateFromStoredInstantiate';
		$this->createClassPlainSimple($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$this->Container->create($class, true);

		$instances = $this->Container->list_instances();
		$this->assertTrue(isset($instances[$class]));
		$res = $this->Container->create($class);

		$this->assertInstanceOf($class, $res);
	}

	public function testCreateFromInstantiate()
	{
		$class = 'TestClassCreateFromInstantiate';
		$this->createClassPlainSimple($class, true);
		$path = $this->getClassLocationPath($class);

		static::mockAutoload($class);
		$res = $this->Container->create($class);

		$this->assertInstanceOf($class, $res);
	}

	public function testCreateFromInstantiateFailure()
	{
		$class = 'TestClassCreateFromInstantiateFailure';
		$this->createClassPlainSimpleInstantiateFailure($class, true);
		$path = $this->getClassLocationPath($class);

				$this->expectException(Exception::class);
				$this->expectExceptionMessage('CONTAINER_INSTANTIATE_ERR; could not instantiate \'TestClassCreateFromInstantiateFailure\', failure');

		static::mockAutoload($class);
		$res = $this->Container->create($class);

// 		$this->assertInstanceOf($class, $res);
	}

	public function testCreateFromInstantiateUnknownClassException()
	{
		$class = 'TestUnknown';

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('CONTAINER_INSTANTIATE_ERR; could not reflect TestUnknown, Class "TestUnknown" does not exist');

		$this->Container->create($class);
	}

	public function testCall()
	{
		$class = 'TestClassCall';
		$this->createClassWithSimpleMethod($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$obj = $this->Container->create($class);

		$res = $this->Container->call($obj, 'test');
		$this->assertTrue($res);
	}

	public function testCallAndStoreReflection()
	{
		$class = 'TestClassCall';
		$this->createClassWithSimpleMethod($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$obj = $this->Container->create($class);

		$res = $this->Container->call($obj, 'test', store_reflection: true);
		$this->assertTrue($res);

		$res = $this->Container->call($obj, 'test');
		$this->assertTrue($res);
	}

	public function testCallWithArgs()
	{
		$class = 'TestClassCallWithArgs';
		$this->createClassWithSimpleMethodWithArgs($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$obj = $this->Container->create($class);

// TODO:  callable and iterable types

		$res = $this->Container->call($obj, 'test', [
			1, 'foo', ['c' => 'C'], true, 0.1
		]);
		$exp = [
			1, 'foo', ['c' => 'C'], true, 0.1,
			2, 'S2', ['A2'], false, 0.2
		];

		$this->assertEquals($exp, $res);
	}

	public function testCallWithNullableArgs()
	{
		$class = 'TestClassCallWithNullableArgs';
		$this->createClassWithSimpleMethodWithNullableArgs($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$obj = $this->Container->create($class);

		$res = $this->Container->call($obj, 'test');
		$exp = [null, null, null, null];
		$this->assertEquals($exp, $res);

		$res = $this->Container->call($obj, 'test', [1]);
		$exp = [1, null, null, null];
		$this->assertEquals($exp, $res);

		$res = $this->Container->call($obj, 'test', [null, 'a']);
		$exp = [null, 'a', null, null];
		$this->assertEquals($exp, $res);

		$res = $this->Container->call($obj, 'test', [null, null, ['A']]);
		$exp = [null, null, ['A'], null];
		$this->assertEquals($exp, $res);

		$res = $this->Container->call($obj, 'test', [null, null, null, true]);
		$exp = [null, null, null, true];
		$this->assertEquals($exp, $res);
	}

	public function testCallWithObjectArgs()
	{
		$class = 'TestClassCallWithObjectArgs';
		$classObject = $class . 'Object';
		$this->createClassWithSimpleMethodWithObjectArgs($class, $classObject, true);
		$path = $this->getClassLocationPath($class);
		$pathObject = $this->getClassLocationPath($classObject);

		$this->Container->locate($class, $path);
		$this->Container->locate($classObject, $pathObject);
		$obj = $this->Container->create($class);

		$res = $this->Container->call($obj, 'test');
		$exp = 'abc';

		$this->assertEquals($exp, $res);
	}

	public function testCallWithObjectArgsStoreForceNew()
	{
		$class = 'TestClassCallWithObjectArgsStoreForceNew';
		$classObject = $class . 'Object';
		$this->createClassWithSimpleMethodWithObjectArgsStoreForceNew($class, $classObject, true);
		$path = $this->getClassLocationPath($class);
		$pathObject = $this->getClassLocationPath($class . 'Object');

		$this->Container->locate($class, $path);
		$this->Container->locate($classObject, $pathObject);
		$obj = $this->Container->create($class);

		$res = $this->Container->call($obj, 'test');
		$exp = 0;
		$this->assertEquals($exp, $res, 'Should create new object');

		$res = $this->Container->call($obj, 'test', [], true);
		$exp = 0;
		$this->assertEquals($exp, $res, 'Should create new object and store');

		$res = $this->Container->call($obj, 'test');
		$exp = 1;
		$this->assertEquals($exp, $res, 'Should call stored object');

		$res = $this->Container->call($obj, 'test');
		$exp = 2;
		$this->assertEquals($exp, $res, 'Should call stored object again');

		$res = $this->Container->call($obj, 'test', [], false, true);
		$exp = 0;
		$this->assertEquals($exp, $res, 'Should create new object and leave stored object');

		$res = $this->Container->call($obj, 'test', [], false, true);
		$exp = 0;
		$this->assertEquals($exp, $res, 'Should create new object and leave stored object again');

		$res = $this->Container->call($obj, 'test');
		$exp = 3;
		$this->assertEquals($exp, $res, 'Should call original stored object');

		$res = $this->Container->call($obj, 'test', [], true, true);
		$exp = 0;
		$this->assertEquals($exp, $res, 'Should create new object and store');

		$res = $this->Container->call($obj, 'test');
		$exp = 1;
		$this->assertEquals($exp, $res, 'Should call new stored object');

		$res = $this->Container->call($obj, 'test', [], false, true);
		$exp = 0;
		$this->assertEquals($exp, $res, 'Should create new object again');
	}

	public function testCallReflectionFailure()
	{
// 		$class = 'TestClassCall';
// 		$this->createClassWithSimpleMethod($class, true);
// 		$path = $this->getClassLocationPath($class);
// 
// 		$this->Container->locate($class, $path);
// 		$obj = $this->Container->create($class);

				$this->expectException(Exception::class);
				$this->expectExceptionMessage('Could not call stdClass::test');
// prx($this->Container->call((object)[], 'test22'));
		$res = $this->Container->call((object)[], 'test');

// 		$this->assertFalse($res);
	}

	public function testCallWithObjectNullableUnionArgs()
	{
		$class = 'TestClassCallWithObjectNullableUnionArgs';

		$this->createClassWithSimpleMethodWithNullableUnionArgs($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$obj = $this->Container->create($class);

		$res = $this->Container->call($obj, 'test');
		$this->assertNull($res, 'Should default to NULL');

		$res = $this->Container->call($obj, 'test', [null]);
		$this->assertNull($res, 'Should be setable as NULL');

		$res = $this->Container->call($obj, 'test', [false]);
		$this->assertFalse($res, 'Should setable as FALSE');

		$res = $this->Container->call($obj, 'test', [100]);
		$this->assertEquals(100, $res, 'Should setable as INT');
	}

	public function testCallWithObjectUnionArgsFailure()
	{
		$class = 'TestClassCallWithObjectUnionArgs';

		$this->createClassWithSimpleMethodWithUnionArgs($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$obj = $this->Container->create($class);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('CONTAINER_CALL_ERR; Could not call TestClassCallWithObjectUnionArgs::test, cannot resolve paramter i');

		$res = $this->Container->call($obj, 'test');
	}

	public function testCallWithObjectReqArgsFailure()
	{
		$class = 'TestClassCallWithObjectReqArgs';

		$this->createClassWithSimpleMethodWithReqArgs($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$obj = $this->Container->create($class);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('CONTAINER_CALL_ERR; Could not call TestClassCallWithObjectReqArgs::test, cannot resolve paramter i');

		$res = $this->Container->call($obj, 'test');
	}

	public function testCallWithReqObjectArg()
	{
		$class = 'TestClassCallWithReqObjectArg';
		$classObject = $class . 'Object';
		$this->createClassWithSimpleMethodWithObjectArgsStoreForceNew($class, $classObject, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$obj = $this->Container->create($class);

		static::mockAutoload($classObject);

		$res = $this->Container->call($obj, 'test');
		$this->assertEquals(0, $res, 'Should create new object using get_class_name_from_type()');

		$res = $this->Container->call($obj, 'test');
		$this->assertEquals(1, $res, 'Should create new object using get_class_name_from_type()');
	}

	public function testCallWithReqInterfaceArg()
	{
		$class = 'TestClassCallWithReqInterfaceArg';
		$classObject = $class . 'Object';
		$this->createClassWithSimpleMethodWithInterfaceArgs($class, $classObject, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$obj = $this->Container->create($class);

		static::mockAutoload($classObject . 'Interface');
		static::mockAutoload($classObject);

		$res = $this->Container->call($obj, 'test');
		$this->assertEquals(0, $res, 'Should create new object using get_class_name_from_type()');
	}

	public function testCacheName()
	{
		$res = $this->Container->cache_name('class');
		$this->assertIsstring($res);
	}

	public function testCachePut()
	{
		$res = $this->Container->cache_put('class');
		$this->assertFalse($res, 'Put cache is FALSE if no store_path');

		$this->Container->set_store(static::$tmpdir);

		$class = 'TestClassCachePutInstance';
		$obj = (object)['test' => true];
		$res = $this->Container->cache_put($class, $obj);

		$this->assertTrue($res, 'Can put instance to cache');

		$class = 'TestClassCachePut';
		$this->createClassPlainSimple($class, true);
		$path = $this->getClassLocationPath($class);

		$this->Container->locate($class, $path);
		$this->Container->create($class, true);
		$res = $this->Container->cache_put($class);

		$this->assertTrue($res, 'Can put existing indexed instance to cache');
	}

	public function testGetCache()
	{
		$res = $this->Container->cache_get('class');
		$this->assertFalse($res, 'Cache is FALSE if no store_path');

		$this->Container->set_store(static::$tmpdir);

		$res = $this->Container->cache_get('class');
		$this->assertFalse($res, 'Cache is FALSE if file not readable');

		file_put_contents(
			static::$tmpdir . '/' . $this->Container->cache_name('class'),
			'test'
		);
		$res = $this->Container->cache_get('class');
		$this->assertFalse($res, 'Cache is FALSE if cannot unserialize');

		file_put_contents(
			static::$tmpdir . '/' . $this->Container->cache_name('class'),
			'O:1:"A":0:{}'
		);

		$res = $this->Container->cache_get('class');
		$this->assertTrue($res, 'Cache is TRUE if can unserialize');
	}

	public function testGet()
	{
		$class = 'TestClassGet';
		$className = 'TestClassGetName';
		$this->createClassPlainSimple($class, true);

		$path = $this->getClassLocationPath($class);
		$this->Container->locate($className, $path);

		$res = $this->Container[$className];

		$this->assertInstanceOf($class, $res);
		$this->assertEquals('A', $res->A);
	}

	public function testGetAssumedLocation()
	{
		$class = 'TestClassGetAssumedLocation';
		$this->createClassPlainSimple($class, true);

		$this->Container->set_locate_path(static::$dir_locations);

		$res = $this->Container[$class];
		$this->assertInstanceOf($class, $res, 'If class not registered or located, should assume register closure in locations path');
	}

	public function testGetFromRegistry()
	{
		$class = 'TestClassGetFromRegistry';
		$this->createClassPlainSimple($class, true);

		$closure = $this->getClassClosure($class);

		$this->Container->register($class, $closure);
		$res = $this->Container[$class];

		$this->assertInstanceOf($class, $res);
	}

	public function testGetFromInstantiate()
	{
		$class = 'TestClassGetFromInstantiate';
		$this->createClassPlainSimple($class);

		static::mockAutoload($class);

		$res = $this->Container[$class];

		$this->assertInstanceOf($class, $res);
	}

	public function testGetFromAlias()
	{
		$class = 'TestClassGetFromAlias';
		$this->createClassPlainSimple($class, true);

		$path = $this->getClassLocationPath($class);
		$this->Container->locate($class, $path);

		$alias = 'TestClassAlias';
		$this->Container->set_alias($alias, $class);

		$res = $this->Container[$alias];

		$this->assertInstanceOf($class, $res);

		$alias2 = 'TestClassAlias2';
		$this->Container->set_alias($alias2, $class);

		$res = $this->Container[$alias2];

		$this->assertInstanceOf($class, $res);
	}

	public function testExists(): void
	{
		$res = $this->Container->exists('zzz');
		$this->assertFalse($res);
	}

	public function testOffset()
	{
		$class = 'TestClassUnset';
		$this->createClassPlainSimple($class);

		static::mockAutoload($class);

		/*$res = */$this->Container[$class];
// 		$this->assertInstanceOf($class, $res);

		//"Could not set $offset"

		$this->Container->offsetSet($class, new $class);

		$res = $this->Container->offsetExists($class);
		$this->assertTrue($res);

//		$null =
			$this->Container->offsetUnset($class);
//		$this->assertNull($null);

		$res = $this->Container->offsetExists($class);
		$this->assertFalse($res);

// 		$closure = $this->Container->register(
// 			$class.'2', );
		$this->Container->offsetSet($class.'2', function() use($class): Closure {
			return new $class;
		});

		$this->assertTrue(isset($this->Container->list_registry()['TestClassUnset2']));
	}

	public function testOffsetException()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Could not set 111');

		$this->Container->offsetSet(111, false);
	}

	protected function createClassPlainSimple(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public \$A = 'A';
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
		}
	}

	protected function createClassPlainSimpleInstantiateFailure(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public \$A = 'A';
	
	public function __construct()
	{
		throw new Exception('failure');
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
		}
	}

	protected function createClassCreateFromInstantiateNoConstructorFailure(string $className/*, bool $createRegisterFile = false*/): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
trait RequiresArgsTrait {
    public function __construct(bool \$arg1, int \$arg2) {
        // this constructor requires two arguments
    }
}

class $className {
    use RequiresArgsTrait;

    // this has no constructor in reflection
}
__
		);

// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
	}

	protected function createClassFailure(string $className, int $createRegisterFile = 0): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public \$A = 'A';

	public function __construct()
	{
		throw new \Exception('Error example');
	}
}
__
		);

		if ($createRegisterFile === 1) {
			file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
<?php
	PARSE FAILURE
	};
__
			);
		} else if ($createRegisterFile === 2) {
			file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
<?php
	throw new \Exception('Exception thrown');
__
			);
		} else if ($createRegisterFile === 3) {
			file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
<?php
return 1;
__
			);

		}
// TODO: set up a parse error
	}

	protected function createClassPlainSimpleReturnFailure(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public \$A = 'A';

	public function __construct()
	{
		throw new \Exception('Error example');
	}
}
__
		);

		if ($createRegisterFile) {
			file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
<?php

// no return

__
		);

		}
// TODO: set up a parse error
	}

	protected function createClassWithArgsInConstructor(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public int \$a;
	public int \$b;

	public function __construct(?int \$a, ?int \$b)
	{
		\$this->a = !is_null(\$a) ? \$a : 0;
		\$this->b = !is_null(\$b) ? \$b : 0;
	}
}
__
		);
	}

	protected function createClassWithDependencyInContructor(string $className, bool $createRegisterFile = false): void
	{
		$class2 = $className . '2';
		$this->createClassPlainSimple($class2, $createRegisterFile);

		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
use $class2;

class $className
{
	public $className \$$class2;

	public function __construct($class2 \$$class2)
	{
		\$this->$class2 = \$$class2;
	}
}
__
		);

		if ($createRegisterFile) {
			file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
<?php
return function (\$Container) {
	PHPCanvas\ContainerTest::mockAutoload('$className');

	return new $className(\$Container['$class2']);
};
__
			);
		}
	}

	protected function createClassWithEmptyContructor(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	protected bool \$b;

	public function __construct()
	{
		\$this->b = true;
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
		}
	}

	protected function createClassWithSimpleMethod(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public function test(): bool
	{
		return true;
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
		}
	}

	protected function createClassWithSimpleMethodWithArgs(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public function test(
		int \$i, string \$s, array \$a, bool \$b, float \$f,
		int \$i2 = 2, string \$s2 = 'S2', array \$a2 = ['A2'], bool \$b2 = false, float \$f2 = 0.2
	): array
	{
		return [
			\$i, \$s, \$a, \$b, \$f,
			\$i2, \$s2, \$a2, \$b2, \$f2
		];
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
		}
	}

	protected function createClassWithSimpleMethodWithNullableArgs(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public function test(?int \$a = null, ?string \$b = null, ?array \$c = null, ?bool \$d = null): array
	{
		return [\$a, \$b, \$c, \$d];
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
		}
	}

	protected function createClassWithSimpleMethodWithObjectArgs(string $className, string $classNameObject, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public function test($classNameObject \$$classNameObject): string
	{
		return \${$classNameObject}->test();
	}
}
__
		);

		file_put_contents(
			static::$dir_classes . "/$classNameObject.php",
			<<<__
<?php
class $classNameObject
{
	public function test(): string
	{
		return 'abc';
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
			$this->createSimpleClassRegister($classNameObject);
		}
	}

	protected function createClassWithSimpleMethodWithObjectArgsStoreForceNew(string $className, string $classNameObject, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public function test($classNameObject \$$classNameObject): int
	{
		return \${$classNameObject}->test();
	}
}
__
		);

		file_put_contents(
			static::$dir_classes . "/$classNameObject.php",
			<<<__
<?php
class $classNameObject
{
	protected int \$i = 0;

	public function test(): int
	{
		return \$this->i++;
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
			$this->createSimpleClassRegister($classNameObject);
		}
	}

	protected function createClassWithSimpleMethodWithInterfaceArgs(string $className, string $classNameObject, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public function test({$classNameObject}Interface \$$classNameObject): int
	{
		return \${$classNameObject}->test();
	}
}
__
		);

		file_put_contents(
			static::$dir_classes . "/{$classNameObject}Interface.php",
			<<<__
<?php
interface {$classNameObject}Interface
{
	public function test(): int;
}
__
		);

		file_put_contents(
			static::$dir_classes . "/$classNameObject.php",
			<<<__
<?php
class $classNameObject implements {$classNameObject}Interface
{
	protected int \$i = 0;

	public function test(): int
	{
		return \$this->i++;
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
			$this->createSimpleClassRegister($classNameObject);
		}
	}

	protected function createClassWithSimpleMethodWithNullableUnionArgs(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public function test(null|false|int \$i): null|false|int
	{
		return \$i;
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
		}
	}

	protected function createClassWithSimpleMethodWithUnionArgs(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public function test(false|int \$i): false|int
	{
		return \$i;
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
		}
	}

	protected function createClassWithSimpleMethodWithReqArgs(string $className, bool $createRegisterFile = false): void
	{
		file_put_contents(
			static::$dir_classes . "/$className.php",
			<<<__
<?php
class $className
{
	public function test(int \$i): int
	{
		return \$i;
	}
}
__
		);

		if ($createRegisterFile) {
			$this->createSimpleClassRegister($className);
		}
	}

	protected function createSimpleClassRegister($className): void
	{
		file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
<?php
return function (\$Container) {
	PHPCanvas\ContainerTest::mockAutoload('$className');

	return new $className();
};
__
		);
	}

// 	protected function getClassPath(string $class): string
// 	{
// 		return static::$dir_classes . "/$class.php";
// 	}

	protected function getClassLocationPath(string $class): string
	{
		return static::$dir_locations . "/register.$class.php";
	}

	protected function getClassClosure(string $class): \Closure
	{
		return include static::$dir_locations . "/register.$class.php";
	}


// 	public function testInstantiate()
// 	{
// 		$class = 'TestClassInstantiate';
// 		$this->createClassPlainSimple($class);
// 		self::mockAutoload($class);
// 
// 		$res = $this->Container->instantiate($class);
// 
// 		$this->assertInstanceOf($class, $res);
// 	}

// 	public function testInstantiateNamedClass()
// 	{
// 		stantiate(string $class_name, ?string $name = null
// 	}

// 	public function testInstantiateWithContructor()
// 	{
// 		$class = 'TestClassInstantiateWithContructor';
// 		$this->createClassWithArgsInConstructor($class);
// 		self::mockAutoload($class);
// 
// 		// all args
// 		$res = $this->Container->instantiate($class, null, [11, 12]);
// 		$this->assertInstanceOf($class, $res);
// 		$this->assertEquals(11, $res->a);
// 		$this->assertEquals(12, $res->b);
// 
// 		// partial args
// 		$res = $this->Container->instantiate($class, null, [1 => 99]);
// 		$this->assertInstanceOf($class, $res);
// 		$this->assertEquals(0, $res->a);
// 		$this->assertEquals(99, $res->b);
// 
// 		// emtpy
// 		$class = 'TestClassWithEmptyContructor';
// 		$this->createClassWithEmptyContructor($class);
// 		include_once static::$dir_classes . '/' . $class . '.php';
// 
// 		$res = $this->Container->instantiate($class, null, []);
// 
// 		$this->assertInstanceOf($class, $res);
// 	}

// 	public function testInstantiateWithName()
// 	{
// 		
// 	}

// 	public function testInstantiateFromRegistry()
// 	{
// 		$class = 'TestClassInstantiateFromRegistry';
// 		$this->createClassPlainSimple($class, true);
// 		self::mockAutoload($class);
// 		$closure = $this->getClassClosure($class);
// 
// 		$this->Container->register($class, $closure);
// 		$res = $this->Container->instantiate($class);
// 
// 		$this->assertInstanceOf($class, $res);
// 	}

// 	public function testInstantiateFromLocations()
// 	{
// 		$class = 'TestClassInstantiateFromLocations';
// 		$this->createClassPlainSimple($class, true);
// 		self::mockAutoload($class);
// 		$path = $this->getClassLocationPath($class);
// 
// 		$this->Container->locate($class, $path);
// 		$res = $this->Container->instantiate($class);
// 
// 		$this->assertInstanceOf($class, $res);
// 	}
}
