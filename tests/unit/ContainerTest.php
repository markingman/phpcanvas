<?php

namespace PHPCanvas;

use Closure;
use PHPCanvas\Exception\ContainerException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use BadMethodCallException;

class ContainerTest extends TestCase
{
	use TestHelpersTrait;

	private string $dir_store;
	private string $dir_fixtures;
	private string $dir_classes;
	private string $dir_locations;
	protected ContainerInterface $Container;

	public static function mockAutoload(string $class): void
	{
		include_once static::$dir_classes . '/' . $class . '.php';
	}

	public static function setUpBeforeClass(): void
	{
		static::tmpdir_make();
	}

	public static function tearDownAfterClass(): void
	{
		static::tmpdir_remove();
	}

	public function setUp(): void
	{
		$this->Container = new Container();

		$this->dir_store = static::$tmpdir;
		if (!is_dir($this->dir_store)) {
			throw new RuntimeException('Could not find cache dir');
		}

		if (!$this->dir_fixtures = (string)realpath(__DIR__ . '/../fixtures')) {
			throw new RuntimeException('Could not find fixtures dir');
		}

		if (!$this->dir_classes = (string)realpath($this->dir_fixtures . '/classes')) {
			throw new RuntimeException('Could not find classes dir');
		}

		if (!$this->dir_locations = (string)realpath($this->dir_fixtures . '/registry')) {
			throw new RuntimeException('Could not find locations dir');
		}
	}

	public function testCreate(): void
	{
		$this->assertInstanceOf(ContainerInterface::class, $this->Container);
	}

	public function testCreateWithStoreAndLocate(): void
	{
		$Container = new Container($this->dir_store, $this->dir_locations);
		$this->assertSame($this->dir_store, $Container->get_store(), 'Can set store path on construct');
		$this->assertSame($this->dir_locations, $Container->get_locate_path(), 'Can locations path on construct');
	}

	public function testStore(): void
	{
		$this->assertSame('', $this->Container->get_store(), 'Unset store path is empty');

		$this->Container->set_store($this->dir_store);
		$this->assertSame($this->dir_store, $this->Container->get_store(), 'Can set and get the store path');
	}

	public function testLocatePath(): void
	{
		$this->assertSame('', $this->Container->get_locate_path(), 'Unset locate path is empty');

		$this->Container->set_locate_path($this->dir_locations);
		$this->assertSame($this->dir_locations, $this->Container->get_locate_path(), 'Can set and get the locations path');
	}

	public function testAlias(): void
	{
		$this->assertNull($this->Container->get_alias('Class1'));

		$this->Container->set_alias('Class1', 'Class2');
		$this->assertSame('Class2', $this->Container->get_alias('Class1'));
	}

	public function testLocate(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->locate('TestClassPlainSimple', $this->dir_classes . '/TestClassPlainSimple.php');

		$this->assertEquals(
			['TestClassPlainSimple' => [$this->dir_classes . '/TestClassPlainSimple.php']],
			$this->Container->list_locations()
		);
	}

	public function testLocateWithArguments(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->locate(
			'TestClassPlainSimple', 
			$this->dir_classes . '/TestClassPlainSimple.php',
			['a' => 123, 'b' => 'test']
		);

		$res = $this->Container->list_locations();

		$this->assertEquals(
			['TestClassPlainSimple' => 
				[$this->dir_classes . '/TestClassPlainSimple.php', ['a' => 123, 'b' => 'test']]],
			$this->Container->list_locations()
		);
	}

	public function testRegister(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->register('TestClassPlainSimple', function(): \PHPCanvas\Test\TestClassPlainSimple {
			return new \PHPCanvas\Test\TestClassPlainSimple();
		});

		$this->assertEquals(
			[
				'TestClassPlainSimple' => function(): \PHPCanvas\Test\TestClassPlainSimple {
						return new \PHPCanvas\Test\TestClassPlainSimple();
				}
			],
			$this->Container->list_registry(),
			'Can register a simple class'
		);
	}

	public function testCreateFromLocation(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->locate('TestClassPlainSimple', $this->dir_locations . '/TestClassPlainSimple.php');

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class,
			$this->Container->create('TestClassPlainSimple'),
			'Can create class from explicitly set location path'
		);
	}


	public function testCreateFromAssumedLocation(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->set_locate_path($this->dir_locations);

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class,
			$this->Container->create('TestClassPlainSimple'),
			'Can create class from explicitly set location path'
		);
	}

	public function testCreateFromLocationWithParseFailure(): void
	{
		// calling closure throws an exception

		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->set_locate_path(realpath(static::$tmpdir));
		if (!file_put_contents(
			static::$tmpdir . '/ParseFailure.php', '<?php PARSE FAILURE };'
		)) {
			throw RuntimeException('Could not create parse failure test file');
		}

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_LOAD_FAILURE; Could not create \'ParseFailure\'; syntax error');
		$this->expectExceptionCode(500);

		$this->Container->create('ParseFailure');
	}

	public function testCreateFromLocationWithImmedateExceptionFailure(): void
	{
		// calling closure throws an exception

		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->set_locate_path($this->dir_locations);

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_LOAD_FAILURE; Could not create \'throw_exception_error\'; Test runtime exception');
		$this->expectExceptionCode(500);

		$this->Container->create('throw_exception_error');
	}

	public function testCreateFromLocationWithReturnTypeFailure(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->set_locate_path($this->dir_locations);

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_TYPE_FAILURE; Location for \'return_true\' must return \\Closure');
		$this->expectExceptionCode(500);

		$this->Container->create('return_true');
	}

	public function testCreateFromLocationWithClosureExceptionFailure(): void
	{
		// calling closure throws an exception

		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->set_locate_path($this->dir_locations);

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_CREATE_FAILURE; Could not create \'TestClosureWithException\'; Test runtime exception');
		$this->expectExceptionCode(500);

		$this->Container->create('TestClosureWithException');
	}

	public function testCreateFromLocationWithClosureReturnNotObject(): void
	{
		// calling closure throws an exception

		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->set_locate_path($this->dir_locations);

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_NOT_OBJECT; Object not created for \'TestClosureBoolReturn\'');
		$this->expectExceptionCode(500);

		$this->Container->create('TestClosureBoolReturn');
	}

	// to

	// TODO:
	// testCreateFromLocationNotLocationNoRegistrationConstructorFailure
	// testCreateFromLocationNotLocationNoRegistrationSetParamsFailure

	public function testCreateFromRegistry(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->register('TestClassPlainSimple', function(): \PHPCanvas\Test\TestClassPlainSimple {
			return new \PHPCanvas\Test\TestClassPlainSimple();
		});

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class,
			$this->Container->create('TestClassPlainSimple'),
			'Can create class from explicit registration'
		);
	}

	public function testCreateFromRegistryAndStore(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->register('TestClassPlainSimple', function(): \PHPCanvas\Test\TestClassPlainSimple {
			return new \PHPCanvas\Test\TestClassPlainSimple();
		});

		$this->Container->create('TestClassPlainSimple');
		$this->assertArrayNotHasKey('TestClassPlainSimple', $this->Container->list_instances());

		$this->Container->create('TestClassPlainSimple', true);
		$this->assertArrayHasKey('TestClassPlainSimple', $this->Container->list_instances());
	}

	public function testCreateFromRegistryWithClosureExceptionFailure(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->register('TestClassWithException', function(): void {
			throw new RuntimeException('Test runtime exception');
		});

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_CREATE_FAILURE; Could not create \'TestClassWithException\'');
		$this->expectExceptionCode(500);

		$this->Container->create('TestClassWithException');
	}

	public function testCreateFromRegistryWithClosureReturnNotObject(): void
	{
		// calling closure throws an exception

		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());

		$this->Container->register('TestClosureBoolReturn', function(): bool {
			return true;
		});

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_NOT_OBJECT; Object not created for \'TestClosureBoolReturn\'');
		$this->expectExceptionCode(500);

		$this->Container->create('TestClosureBoolReturn');
	}

			// 	public function testCreateFromStoredInstantiate()
			// 	{
			// 		$this->Container->register('TestClassPlainSimple', function(): \PHPCanvas\Test\TestClassPlainSimple {
			// 			return new \PHPCanvas\Test\TestClassPlainSimple();
			// 		});
			// 
			// 		$this->Container->create('TestClassPlainSimple', true);
			// 
			// 		$this->assertInstanceOf(
			// 			\PHPCanvas\Test\TestClassPlainSimple::class, 
			// 			$this->Container->create('TestClassPlainSimple')
			// 		);
			// 	}

	public function testCreateFromInstantiate()
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());
		$this->assertSame('', $this->Container->get_locate_path());

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class, 
			$this->Container->create(\PHPCanvas\Test\TestClassPlainSimple::class)
		);
	}

	public function testCreateFromInstantiateAndStore()
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());
		$this->assertSame('', $this->Container->get_locate_path());

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class, 
			$this->Container->create(\PHPCanvas\Test\TestClassPlainSimple::class, true)
		);
	}

	public function testCreateFromInstantiateFailureNotExists(): void
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());
		$this->assertSame('', $this->Container->get_locate_path());

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_CLASS_NOT_FOUND; Could not find \'ClassDoesNotExist\'');
		$this->expectExceptionCode(500);

		$this->Container->create('ClassDoesNotExist');
	}

	public function testCreateFromInstantiateFailureException()
	{
		$this->assertSame([], $this->Container->list_locations());
		$this->assertSame([], $this->Container->list_registry());
		$this->assertSame([], $this->Container->list_instances());
		$this->assertSame('', $this->Container->get_locate_path());

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_INSTANTIATE_FAILURE; Could not instantiate \'' . \PHPCanvas\Test\TestClassWithException::class . '\'');
		$this->expectExceptionCode(500);

		$this->Container->create(\PHPCanvas\Test\TestClassWithException::class);
	}

// 	public function testCreateFromInstantiateFailure()
// 	{
// 		$class = 'TestClassCreateFromInstantiateFailure';
// 		$this->createClassPlainSimpleInstantiateFailure($class, true);
// 		$path = $this->getClassLocationPath($class);
// 
// 		$this->expectException(Exception::class);
// 		$this->expectExceptionMessage('CONTAINER_INSTANTIATE_ERR; could not instantiate \'TestClassCreateFromInstantiateFailure\', failure');
// 
// 		static::mockAutoload($class);
// 		$res = $this->Container->create($class);
// 
// // 		$this->assertInstanceOf($class, $res);
// 	}
// 
// 	public function testCreateFromInstantiateUnknownClassException()
// 	{
// 		$class = 'TestUnknown';
// 
// 		$this->expectException(Exception::class);
// 		$this->expectExceptionMessage('CONTAINER_INSTANTIATE_ERR; could not reflect TestUnknown, Class "TestUnknown" does not exist');
// 
// 		$this->Container->create($class);
// 	}
// 
	public function testCall()
	{
		$this->assertTrue($this->Container->call(
			$this->Container->create(\PHPCanvas\Test\TestClassWithSimpleMethods::class), 'test'
		));
	}

	public function testCallNoMethod()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('CONTAINER_CALL_ERR; Not callable ' . \PHPCanvas\Test\TestClassWithSimpleMethods::class . '::test2');
		$this->expectExceptionCode(500);

		$this->assertTrue($this->Container->call(
			$this->Container->create(\PHPCanvas\Test\TestClassWithSimpleMethods::class), 'test2'
		));
	}

	public function testCallAndStoreReflection()
	{
		$obj = $this->Container->create(\PHPCanvas\Test\TestClassWithSimpleMethods::class);

		$this->assertTrue($this->Container->call($obj, 'test', store_reflection: true));

		$this->assertTrue($this->Container->call($obj, 'test'));
	}

	public function testCallWithArgs(): void
	{
		$obj = $this->Container->create(\PHPCanvas\Test\TestClassWithArguments::class);

		$this->assertEquals([
			1,
			'foo',
			['c' => 'C'],
			true,
			0.1,
			2,
			'S2',
			['A2'],
			false,
			0.2
		], $this->Container->call($obj, 'test', [
			1,
			'foo',
			['c' => 'C'],
			true,
			0.1
		]));
	}

	public function testCallWithNullableArgs(): void
	{
		$obj = $this->Container->create(\PHPCanvas\Test\TestClassWithNullableArguments::class);

		$this->assertEquals([null, null, null, null], $this->Container->call($obj, 'test'));

		$this->assertEquals([1, null, null, null], $this->Container->call($obj, 'test', [1]));

		$this->assertEquals([null, 'a', null, null], $this->Container->call($obj, 'test', [null, 'a']));

		$this->assertEquals([null, null, ['A'], null], $this->Container->call($obj, 'test', [null, null, ['A']]));

		$this->assertEquals([null, null, null, true], $this->Container->call($obj, 'test', [null, null, null, true]));
	}

	public function testCallWithObjectArgs()
	{
		$this->Container->locate('TestClassWithObjectArguments', $this->dir_locations . '/TestClassWithObjectArguments.php');
		$this->Container->locate('TestClassWithSimpleMethods', $this->dir_locations . '/TestClassWithSimpleMethods.php');

		$this->assertEquals(
			'abc',
			$this->Container->call($this->Container->create('TestClassWithObjectArguments'), 'get_string')
		);
	}

	public function testCallWithObjectArgsStoreForceNew()
	{
		$this->Container->locate('TestClassWithObjectArguments', $this->dir_locations . '/TestClassWithObjectArguments.php');
		$this->Container->locate('TestClassWithSimpleMethods', $this->dir_locations . '/TestClassWithSimpleMethods.php');
		$obj = $this->Container->create('TestClassWithObjectArguments');

		$this->assertEquals(0, $this->Container->call($obj, 'inc'), 'Should create new object');

		$this->assertEquals(0, $this->Container->call($obj, 'inc', store: true), 'Should create new object and store');

		$this->assertEquals(1, $this->Container->call($obj, 'inc'), 'Should call stored object');

		$this->assertEquals(2, $this->Container->call($obj, 'inc'), 'Should call stored object again');

		$this->assertEquals(0, $this->Container->call($obj, 'inc', force_new: true), 'Should create new object and leave stored object');

		$this->assertEquals(0, $this->Container->call($obj, 'inc', force_new: true), 'Should create new object and leave stored object again');

		$this->assertEquals(3, $this->Container->call($obj, 'inc'), 'Should call original stored object');

		$this->assertEquals(0, $this->Container->call($obj, 'inc', store: true, force_new: true), 'Should create new object and store');

		$this->assertEquals(1, $this->Container->call($obj, 'inc'), 'Should call new stored object');

		$this->assertEquals(0, $this->Container->call($obj, 'inc', force_new: true), 'Should create new object again');
	}

	public function testCallUnknownMethod()
	{
		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('Could not call stdClass::test');

		$this->Container->call((object)[], 'test');
	}

	public function testCallWithObjectNullableUnionArgs()
	{
		$obj = $this->Container->create(\PHPCanvas\Test\TestClassWithNullableUnionArgs::class);

		$this->assertNull($this->Container->call($obj, 'test'), 'Should default to NULL');

		$this->assertNull($this->Container->call($obj, 'test', [null]), 'Should be setable as NULL');

		$this->assertFalse($this->Container->call($obj, 'test', [false]), 'Should setable as FALSE');

		$this->assertEquals(100, $this->Container->call($obj, 'test', [100]), 'Should setable as INT');
	}

	public function testCallWithObjectUnionArgsFailure()
	{
		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_CALL_FAILURE; Could not call PHPCanvas\Test\TestClassWithUnionArgs::test; cannot handle union type parameter i');

		$res = $this->Container->call($this->Container->create(\PHPCanvas\Test\TestClassWithUnionArgs::class), 'test');
	}

	public function testCallWithObjectReqArgsFailure()
	{
		$this->Container->locate('TestClassWithSimpleMethods', $this->dir_locations . '/TestClassWithSimpleMethods.php');

		$this->expectException(ContainerException::class);
		$this->expectExceptionMessage('CONTAINER_CALL_FAILURE; Could not call PHPCanvas\Test\TestClassWithSimpleMethods::req; cannot resolve parameter i');

		$res = $this->Container->call($this->Container->create('TestClassWithSimpleMethods'), 'req');
	}

	public function testCallWithReqObjectArg()
	{
		$this->Container->register('TestClassWithObjectArguments', function(): \PHPCanvas\Test\TestClassWithObjectArguments {
			return new \PHPCanvas\Test\TestClassWithObjectArguments();
		});
		$obj = $this->Container->create('TestClassWithObjectArguments');

		$this->assertTrue($this->Container->call($obj, 'test'), 'Should create new object using paramater type');
	}

	public function testCallWithReqInterfaceArg()
	{
		$this->Container->set_locate_path($this->dir_locations);

		$this->assertSame('iface', 
			$this->Container->call($this->Container->create('TestClassWithSimpleMethods'), 'iface'),
			'Should create new object using get_class_name_from_type()');
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

		$this->Container->set_store($this->dir_store);

		$this->assertTrue(
			$this->Container->cache_put('TestClassCachePutDirect', (object)['test' => true]),
			'Can put an object to cache'
		);

		$this->Container->locate('TestClassPlainSimple', $this->dir_locations . '/TestClassPlainSimple.php');
		$this->Container->create('TestClassPlainSimple', true);

		$this->assertTrue(
			$this->Container->cache_put('TestClassPlainSimple'),
			'Can put existing indexed instance to cache'
		);
	}

	public function testGetCache()
	{
		$this->assertFalse($this->Container->cache_get('class'), 'Cache is FALSE if no store_path');

		$this->Container->set_store($this->dir_store);

		$this->assertFalse($this->Container->cache_get('class'), 'Cache is FALSE if file not readable');

		file_put_contents(
			$this->dir_store . DIRECTORY_SEPARATOR . $this->Container->cache_name('class'),
			'test'
		);

		$this->assertFalse($this->Container->cache_get('class'), 'Cache is FALSE if cannot unserialize');

		file_put_contents(
			$this->dir_store . DIRECTORY_SEPARATOR . $this->Container->cache_name('class'),
			serialize(['a'])
		);

		$this->assertFalse($this->Container->cache_get('class'), 'Cache is FALSE if not an object');

		$this->Container->cache_put('TestClassCachePutDirect', (object)['test' => true]);
		$this->assertTrue(
			$this->Container->cache_get('TestClassCachePutDirect'),
			'Must be able to unserialize an object'
		);
			
		$this->Container->locate('TestClassPlainSimple', $this->dir_locations . '/TestClassPlainSimple.php');
		$this->Container->create('TestClassPlainSimple', true);
		$this->Container->cache_put('TestClassPlainSimple');

		$this->assertTrue(
			$this->Container->cache_get('TestClassPlainSimple'),
			'Must be able to unserialize an existing indexed instance'
		);

		$this->assertFalse(
			$this->Container->cache_get('TestClassPlainSimple', 'ClassNotExists'),
			'If instanceof is not match, return false'
		);

		$this->assertTrue(
			$this->Container->cache_get('TestClassPlainSimple', \PHPCanvas\Test\TestClassPlainSimple::class),
			'Must be able to unserialize an existing indexed instance and confirm instanceof'
		);


	}


	public function testGet()
	{
		$this->Container->locate('TestClassPlainSimple', $this->dir_locations . '/TestClassPlainSimple.php');

		$this->assertInstanceOf(\PHPCanvas\Test\TestClassPlainSimple::class, $this->Container->get('TestClassPlainSimple'));
	}

	public function testGetFromSet(): void
	{
		$this->Container->set('TestClassPlainSimple1', function(): \PHPCanvas\Test\TestClassPlainSimple {
			return new \PHPCanvas\Test\TestClassPlainSimple();
		});

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class,
			$this->Container->get('TestClassPlainSimple1')
		);
	}

	public function testGetFromAssumedLocation()
	{
		$this->Container->set_locate_path($this->dir_locations);

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class, 
			$this->Container->get('TestClassPlainSimple'), 
			'If class not registered or located, should assume register closure in locations path'
		);
	}

	public function testGetFromRegistry()
	{
		$this->Container->register('TestClassPlainSimple', function(): \PHPCanvas\Test\TestClassPlainSimple {
			return new \PHPCanvas\Test\TestClassPlainSimple();
		});

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class, 
			$this->Container->get('TestClassPlainSimple'), 
		);
	}

	public function testGetFromAlias()
	{
		$this->Container->locate('TestClassPlainSimple', $this->dir_locations . '/TestClassPlainSimple.php');

		$this->Container->set_alias('TestClassAlias', 'TestClassPlainSimple');

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class, 
			$this->Container->get('TestClassAlias'), 
		);

		$this->Container->set_alias('TestClassAlias2', 'TestClassPlainSimple');

		$this->assertInstanceOf(
			\PHPCanvas\Test\TestClassPlainSimple::class, 
			$this->Container->get('TestClassAlias2'), 
		);

	}

	public function testSet(): void
	{
		// by registry

		$this->assertFalse($this->Container->exists('TestClassPlainSimple1'));

		$this->Container->set('TestClassPlainSimple1', function(): \PHPCanvas\Test\TestClassPlainSimple {
			return new \PHPCanvas\Test\TestClassPlainSimple();
		});

		$this->assertTrue($this->Container->exists('TestClassPlainSimple1'));
		$this->assertArrayHasKey('TestClassPlainSimple1', $this->Container->list_registry());

		// by instance

		$this->assertFalse($this->Container->exists('TestClassPlainSimple2'));

		$this->Container->set('TestClassPlainSimple2', new \PHPCanvas\Test\TestClassPlainSimple());

		$this->assertTrue($this->Container->exists('TestClassPlainSimple2'));
		$this->assertArrayHasKey('TestClassPlainSimple2', $this->Container->list_instances());
	}

	public function testExists(): void
	{
		// by registry

		$this->assertFalse($this->Container->exists('TestClassPlainSimple1'));

		$this->Container->register('TestClassPlainSimple1', function(): \PHPCanvas\Test\TestClassPlainSimple {
			return new \PHPCanvas\Test\TestClassPlainSimple();
		});

		$this->assertTrue($this->Container->exists('TestClassPlainSimple1'));

		// by location

		$this->assertFalse($this->Container->exists('TestClassPlainSimple2'));

		$this->Container->locate('TestClassPlainSimple2', $this->dir_locations . '/TestClassPlainSimple.php');

		$this->assertTrue($this->Container->exists('TestClassPlainSimple2'));

		// by instance
		
		$this->assertFalse($this->Container->exists('TestClassPlainSimple3'));

		$this->Container->set('TestClassPlainSimple3', new \PHPCanvas\Test\TestClassPlainSimple());

		$this->assertTrue($this->Container->exists('TestClassPlainSimple3'));
	}

	public function testUnset(): void
	{
		$this->assertFalse($this->Container->exists('TestClassPlainSimple'));

		$this->Container->register('TestClassPlainSimple', function(): \PHPCanvas\Test\TestClassPlainSimple {
			return new \PHPCanvas\Test\TestClassPlainSimple();
		});
		
		$this->assertArrayNotHasKey('TestClassPlainSimple', $this->Container->list_instances());
		$this->Container->create('TestClassPlainSimple', true);

		$this->assertArrayHasKey('TestClassPlainSimple', $this->Container->list_instances());
		$this->Container->unset('TestClassPlainSimple');

		$this->assertArrayNotHasKey('TestClassPlainSimple', $this->Container->list_instances());
	}

// 	public function testOffset()
// 	{
// 		$class = 'TestClassUnset';
// 		$this->createClassPlainSimple($class);
// 
// 		static::mockAutoload($class);
// 
// 		/*$res = */
// 		$this->Container[$class];
// // 		$this->assertInstanceOf($class, $res);
// 
// 		//"Could not set $offset"
// 
// 		$this->Container->offsetSet($class, new $class);
// 
// 		$res = $this->Container->offsetExists($class);
// 		$this->assertTrue($res);
// 
// //		$null =
// 		$this->Container->offsetUnset($class);
// //		$this->assertNull($null);
// 
// 		$res = $this->Container->offsetExists($class);
// 		$this->assertFalse($res);
// 
// // 		$closure = $this->Container->register(
// // 			$class.'2', );
// 		$this->Container->offsetSet($class . '2', function () use ($class): Closure {
// 			return new $class;
// 		});
// 
// 		$this->assertTrue(isset($this->Container->list_registry()['TestClassUnset2']));
// 	}
// 
// 	public function testOffsetException()
// 	{
// 		$this->expectException(Exception::class);
// 		$this->expectExceptionMessage('Could not set 111');
// 
// 		$this->Container->offsetSet(111, false);
// 	}
// 















// 	protected function createClassPlainSimple(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public \$A = 'A';
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
// 	}
// 
// 	protected function createClassPlainSimpleInstantiateFailure(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public \$A = 'A';
// 	
// 	public function __construct()
// 	{
// 		throw new Exception('failure');
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
// 	}
// 
// 	protected function createClassCreateFromInstantiateNoConstructorFailure(string $className/*, bool $createRegisterFile = false*/): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// trait RequiresArgsTrait {
//     public function __construct(bool \$arg1, int \$arg2) {
//         // this constructor requires two arguments
//     }
// }
// 
// class $className {
//     use RequiresArgsTrait;
// 
//     // this has no constructor in reflection
// }
// __
// 		);
// 
// // 		if ($createRegisterFile) {
// // 			$this->createSimpleClassRegister($className);
// // 		}
// 	}
// 
// 	protected function createClassFailure(string $className, int $createRegisterFile = 0): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public \$A = 'A';
// 
// 	public function __construct()
// 	{
// 		throw new \Exception('Error example');
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile === 1) {
// 			file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
// <?php
// 	PARSE FAILURE
// 	};
// __
// 			);
// 		} else {
// 			if ($createRegisterFile === 2) {
// 				file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
// <?php
// 	throw new \Exception('Exception thrown');
// __
// 				);
// 			} else {
// 				if ($createRegisterFile === 3) {
// 					file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
// <?php
// return 1;
// __
// 					);
// 
// 				}
// 			}
// 		}
// // TODO: set up a parse error
// 	}
// 
// 	protected function createClassPlainSimpleReturnFailure(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public \$A = 'A';
// 
// 	public function __construct()
// 	{
// 		throw new \Exception('Error example');
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
// <?php
// 
// // no return
// 
// __
// 			);
// 
// 		}
// // TODO: set up a parse error
// 	}
// 
// 	protected function createClassWithArgsInConstructor(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public int \$a;
// 	public int \$b;
// 
// 	public function __construct(?int \$a, ?int \$b)
// 	{
// 		\$this->a = !is_null(\$a) ? \$a : 0;
// 		\$this->b = !is_null(\$b) ? \$b : 0;
// 	}
// }
// __
// 		);
// 	}
// 
// 	protected function createClassWithDependencyInContructor(string $className, bool $createRegisterFile = false): void
// 	{
// 		$class2 = $className . '2';
// 		$this->createClassPlainSimple($class2, $createRegisterFile);
// 
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// use $class2;
// 
// class $className
// {
// 	public $className \$$class2;
// 
// 	public function __construct($class2 \$$class2)
// 	{
// 		\$this->$class2 = \$$class2;
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
// <?php
// return function (\$Container) {
// 	PHPCanvas\ContainerTest::mockAutoload('$className');
// 
// 	return new $className(\$Container['$class2']);
// };
// __
// 			);
// 		}
// 	}
// 
// 	protected function createClassWithEmptyContructor(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	protected bool \$b;
// 
// 	public function __construct()
// 	{
// 		\$this->b = true;
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
// 	}
// 
// 	protected function createClassWithSimpleMethod(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public function test(): bool
// 	{
// 		return true;
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
// 	}
// 
// 	protected function createClassWithSimpleMethodWithArgs(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public function test(
// 		int \$i, string \$s, array \$a, bool \$b, float \$f,
// 		int \$i2 = 2, string \$s2 = 'S2', array \$a2 = ['A2'], bool \$b2 = false, float \$f2 = 0.2
// 	): array
// 	{
// 		return [
// 			\$i, \$s, \$a, \$b, \$f,
// 			\$i2, \$s2, \$a2, \$b2, \$f2
// 		];
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
// 	}
// 
// 	protected function createClassWithSimpleMethodWithNullableArgs(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public function test(?int \$a = null, ?string \$b = null, ?array \$c = null, ?bool \$d = null): array
// 	{
// 		return [\$a, \$b, \$c, \$d];
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
// 	}
// 
// 	protected function createClassWithSimpleMethodWithObjectArgs(string $className, string $classNameObject, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public function test($classNameObject \$$classNameObject): string
// 	{
// 		return \${$classNameObject}->test();
// 	}
// }
// __
// 		);
// 
// 		file_put_contents(
// 			static::$dir_classes . "/$classNameObject.php",
// 			<<<__
// <?php
// class $classNameObject
// {
// 	public function test(): string
// 	{
// 		return 'abc';
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 			$this->createSimpleClassRegister($classNameObject);
// 		}
// 	}
// 
// 	protected function createClassWithSimpleMethodWithObjectArgsStoreForceNew(string $className, string $classNameObject, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public function test($classNameObject \$$classNameObject): int
// 	{
// 		return \${$classNameObject}->test();
// 	}
// }
// __
// 		);
// 
// 		file_put_contents(
// 			static::$dir_classes . "/$classNameObject.php",
// 			<<<__
// <?php
// class $classNameObject
// {
// 	protected int \$i = 0;
// 
// 	public function test(): int
// 	{
// 		return \$this->i++;
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 			$this->createSimpleClassRegister($classNameObject);
// 		}
// 	}
// 
// 	protected function createClassWithSimpleMethodWithInterfaceArgs(string $className, string $classNameObject, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public function test({$classNameObject}Interface \$$classNameObject): int
// 	{
// 		return \${$classNameObject}->test();
// 	}
// }
// __
// 		);
// 
// 		file_put_contents(
// 			static::$dir_classes . "/{$classNameObject}Interface.php",
// 			<<<__
// <?php
// interface {$classNameObject}Interface
// {
// 	public function test(): int;
// }
// __
// 		);
// 
// 		file_put_contents(
// 			static::$dir_classes . "/$classNameObject.php",
// 			<<<__
// <?php
// class $classNameObject implements {$classNameObject}Interface
// {
// 	protected int \$i = 0;
// 
// 	public function test(): int
// 	{
// 		return \$this->i++;
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 			$this->createSimpleClassRegister($classNameObject);
// 		}
// 	}
// 
// 	protected function createClassWithSimpleMethodWithNullableUnionArgs(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public function test(null|false|int \$i): null|false|int
// 	{
// 		return \$i;
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
// 	}
// 
// 	protected function createClassWithSimpleMethodWithUnionArgs(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public function test(false|int \$i): false|int
// 	{
// 		return \$i;
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
// 	}
// 
// 	protected function createClassWithSimpleMethodWithReqArgs(string $className, bool $createRegisterFile = false): void
// 	{
// 		file_put_contents(
// 			static::$dir_classes . "/$className.php",
// 			<<<__
// <?php
// class $className
// {
// 	public function test(int \$i): int
// 	{
// 		return \$i;
// 	}
// }
// __
// 		);
// 
// 		if ($createRegisterFile) {
// 			$this->createSimpleClassRegister($className);
// 		}
// 	}
// 
// 	protected function createSimpleClassRegister($className): void
// 	{
// 		file_put_contents(static::$dir_locations . "/register.$className.php", <<<__
// <?php
// return function (\$Container) {
// 	PHPCanvas\ContainerTest::mockAutoload('$className');
// 
// 	return new $className();
// };
// __
// 		);
// 	}

// 	protected function getClassPath(string $class): string
// 	{
// 		return static::$dir_classes . "/$class.php";
// 	}

	protected function getClassLocationPath(string $class): string
	{
		return static::$dir_locations . "/register.$class.php";
	}

	protected function getClassClosure(string $class): Closure
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
