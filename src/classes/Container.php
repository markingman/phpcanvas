<?php

namespace PHPCanvas;

use BadMethodCallException;
use Closure;
use Exception;
use InvalidArgumentException;
use LogicException;
use PHPCanvas\Exception\ContainerError;
use PHPCanvas\Exception\ContainerException;
use ReflectionClass;
use ReflectionParameter;
use ReflectionUnionType;
use RuntimeException;
use Throwable;

class Container implements ContainerInterface
{
	protected string $store_path = '';// serialized classes cache dir
	protected string $locate_path = '';// default registrations dir
	/** @var array<string, array{0: string, 1?: array<mixed>}> $locations */
	protected array $locations = [];// list of file paths to closures
	/** @var array<string, Closure> $registry */
	protected array $registry = [];// list of closures
	/** @var array<string, ReflectionClass<object>> $reflections */
	protected array $reflections = [];// cache of reflections
	/** @var array<string, object> $instances */
	protected array $instances = [];// list of instantiated objects
	/** @var array<string, string> $aliases */
	protected array $aliases = [];// list of name aliases

	public function __construct(?string $store = null, ?string $locate = null)
	{
		if ($store) {
			$this->set_store($store);
		}
		if ($locate) {
			$this->set_locate_path($locate);
		}
	}

	public function set_store(string $path): void
	{
		$this->store_path = $path;
	}

	public function get_store(): string
	{
		return $this->store_path;
	}

	public function get_locate_path(): string
	{
		return $this->locate_path;
	}

	public function set_locate_path(string $path): void
	{
		$this->locate_path = realpath($path) ?: '';
	}

	public function set_alias(string $alias_name, string $target_name): void
	{
		$this->aliases[$alias_name] = $target_name;
	}

	public function get_alias(string $alias_name): ?string
	{
		return $this->aliases[$alias_name] ?? null;
	}

	/** @param ?array<mixed> $args */
	public function locate(string $name, string $path, ?array $args = null): void
	{
		$this->locations[$name] = $args ? [$path, $args] : [$path];
	}

	public function register(string $name, Closure $closure): void
	{
		$this->registry[$name] = $closure;
	}

	public function create(string $name, bool $store = false): object
	{
		if (!isset($this->registry[$name])) {
			if (!isset($this->locations[$name])) {
				if ($this->locate_path) {
					if ($locate_path = $this->get_realpath($name)) {
						$this->locate($name, $locate_path);
					}
				}
			}

			if (isset($this->locations[$name])) {
				$path = $this->locations[$name][0];
				$args = $this->locations[$name][1] ?? [];
				try {
					$closure = (Closure::bind(function () use ($path, $args): mixed {
						extract($args);

						return include $path;
					}, null)());

				} catch (Throwable $e) {
					throw new ContainerException("Could not create '$name'", ContainerError::LOAD_FAILURE, 500, $e);
				}

				if (!($closure instanceof Closure)) {
					throw new ContainerException("Location for '$name' must return \Closure", ContainerError::TYPE_FAILURE, 500);
				}

				$this->register($name, $closure);
			}
		}

		if (isset($this->registry[$name])) {
			try {
				$instance = $this->registry[$name]($this);
			} catch (Exception $e) {
				throw new ContainerException("Could not create '$name'", ContainerError::CREATE_FAILURE, 500, $e);
			}

			if (!is_object($instance)) {
				throw new ContainerException("Object not created for '$name'", ContainerError::NOT_OBJECT, 500);
			}

			if ($store) {
				$this->instances[$name] = $instance;
			}

			return $instance;
		} else {
			try {
				// for this edge case it tries presuming $name is class name
				return $this->instantiate($name, $name, $store);
			} catch (Throwable $e) {
				throw new ContainerException("Could not instantiate '$name'", ContainerError::INSTANTIATE_FAILURE, 500, $e);
			}
		}
	}

	/**
	 * @param array<mixed> $args
	 */
	public function call(object $class, string $method_name, array $args = [], bool $store = false, bool $force_new = false, bool $store_reflection = false): mixed
	{
		$class_name = get_class($class);

//		try {
		$reflection = $this->reflections[$class_name] ?? new ReflectionClass($class_name);
//		} catch (ReflectionException $e) {
//			throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
//		}

		try {
			$method = $reflection->getMethod($method_name);
// if ($method_name === 'get_string'){
//  var_dump($method->getParameters());
// exit('here');}
			$method_params = $method->getParameters();
			$params = ($args or $method_params) ? $this->set_params($method->getParameters(), $args, $store, $force_new) : [];
		} catch (Exception $e) {
			throw new ContainerException("Could not call $class_name::$method_name", ContainerError::CALL_FAILURE, previous: $e);
		}

		if ($store_reflection) {
			$this->reflections[$class_name] = $reflection;
		}

		$callable = [$class, $method_name];
		if (!is_callable($callable)) {
			throw new BadMethodCallException("CONTAINER_CALL_ERR; Not callable $class_name::$method_name", 500);
		}

		return call_user_func_array($callable, $params);
	}

	public function cache_get(string $name, ?string $instanceof = null): bool
	{
		if (!$this->store_path) {
			return false;
		}

		if (!$cache = @file_get_contents($this->store_path . DIRECTORY_SEPARATOR . $this->cache_name($name))) {
			return false;
		}

		if (!$class = @unserialize($cache)) {
			return false;
		}

		if (!is_object($class)) {
			return false;
		}

		if ($instanceof and !$class instanceof $instanceof) {
			return false;
		}

		$this->instances[$name] = $class;

		return true;
	}

	public function cache_put(string $name, mixed $instance = null): bool
	{
		if ($this->store_path) {
			if (!$instance and isset($this->instances[$name])) {
				$instance = $this->instances[$name];
			}

			if ($instance) {
				return (bool)@file_put_contents($this->store_path . DIRECTORY_SEPARATOR . $this->cache_name($name), serialize($instance));
			}
		}

		return false;
	}

	public function cache_name(string $name): string
	{
		return md5($name);
	}

	public function get(string $name, bool $store_created = true): object|null
	{
		$name = $this->aliases[$name] ?? $name;

		return $this->instances[$name] ?? $this->create($name, $store_created);
	}

	public function set(string $name, object $value): void
	{
		if ($value instanceof Closure) {
			$this->register($name, $value);
		} else {
			$this->instances[$name] = $value;
		}
	}

	public function exists(string $name): bool
	{
		$name = $this->aliases[$name] ?? $name;

		return isset($this->instances[$name]) or isset($this->locations[$name]) or isset($this->registry[$name]);
	}

	public function unset(string $name): void
	{
		unset($this->instances[$name]);
	}

	/** @return array<string, array{0: string, 1?: array<mixed>}> */
	public function list_locations(): array
	{
		return $this->locations;
	}

	/** @return array<string, Closure> */
	public function list_registry(): array
	{
		return $this->registry;
	}

	/** @return array<string, object> */
	public function list_instances(): array
	{
		return $this->instances;
	}

	protected function get_realpath(string $name): string|false
	{
		static $paths = [];

		if (!isset($paths[$name])) {
			$realpath = realpath($this->locate_path . DIRECTORY_SEPARATOR . $name . '.php');
			$paths[$name] = (
				$realpath
				and str_starts_with($realpath, $this->locate_path)
				and basename($realpath, '.php') === $name
			) ? $realpath : false;
		}

		return $paths[$name];
	}

	protected function get_class_name_from_type(string $name): string
	{
		if (str_ends_with($name, 'Interface') and ($len = strlen($name)) > 9) { // strlen('Interface') === 9
			$name = substr($name, 0, $len - 9);
		}

		return $name;
	}

	/**
	 * @param array<ReflectionParameter> $params
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	protected function set_params(array $params, array $args = [], bool $store = false, bool $force_new = false): array
	{
		foreach ($params as $i => $param) {
			if (isset($args[$i])) {
				$params[$i] = $args[$i];
				continue;
			}

			$p_name = $param->getName();
			$p_opt = $param->isOptional();
			$p_null = $param->allowsNull();

			if ($p_opt) {
// 				try {
				$params[$i] = $param->getDefaultValue();
// 				} catch (Throwable $e) {
// 					throw new RunTimeException($e->getMessage(), $e->getCode(), $e);
// 				}
				continue;
			}

			if ($p_null) {
				$params[$i] = null;
				continue;
			}

			$p_type = $param->getType();

			if (is_null($p_type)) {
				throw new InvalidArgumentException("cannot resolve parameter $p_name");
			}

			if ($p_type instanceof ReflectionUnionType) {
				throw new LogicException("cannot handle union type parameter $p_name");
			}

			$p_type_name = (string)$p_type;//->getName();

			if (
				$p_type_name !== 'int'
				and $p_type_name !== 'string'
				and $p_type_name !== 'array'
				and $p_type_name !== 'bool'
				and $p_type_name !== 'float'
				and $p_type_name !== 'callable'
				and $p_type_name !== 'iterable'
			) {
				if (!$force_new and isset($this->instances[$p_name])) {
					$params[$i] = $this->instances[$p_name];
				} else {
					$name = $this->aliases[$p_name] ?? $p_name;
					if (
						isset($this->registry[$name])
						or isset($this->locations[$name])
						or ($this->locate_path and $this->get_realpath($name))
					) {
						$params[$i] = $this->create($name, $store);
					} else {
						$class_name = $this->get_class_name_from_type($p_type_name);
						$params[$i] = $this->instantiate($class_name, $p_name, $store);
					}
				}
				continue;
			}

			throw new RuntimeException("cannot resolve parameter $p_name");
		}

		return $params;
	}

	protected function instantiate(string $class_name, ?string $name = null, bool $store = false): object
	{
		if (!class_exists($class_name)) {
			throw new ContainerException("Could not find '$class_name'", ContainerError::CLASS_NOT_FOUND, 500);
		}

		$reflection = new ReflectionClass($class_name);
		$constructor = $reflection->getConstructor();

		try {
			if (!is_null($constructor)) {

				$class = $reflection->newInstanceArgs(
					$this->set_params($constructor->getParameters())
				);
			} else {
				$class = $reflection->newInstanceArgs();
			}
		} catch (Throwable $e) {
			throw new ContainerException("Could not instantiate '$class_name'", ContainerError::INSTANTIATE_FAILURE, 500, $e);
		}

		if ($store) {
			$this->instances[$name ?: $class_name] = $class;
		}

		return $class;
	}
}
