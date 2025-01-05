<?php

namespace PHPCanvas;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionParameter;
use ReflectionUnionType;
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

	/** @throws Exception */
	public function create(string $name, bool $store = false): object
	{
		if (!isset($this->registry[$name])) {
			if (!isset($this->locations[$name])) {
				if ($this->locate_path) {
					// locate
					$locate_path = realpath($this->locate_path . DIRECTORY_SEPARATOR . 'register.' . $name . '.php');
					if ($locate_path and str_starts_with($locate_path, $this->locate_path)) {
						$this->locate($name, $this->locate_path . DIRECTORY_SEPARATOR . 'register.' . $name . '.php');
					}
				}
			}

			if (isset($this->locations[$name])) {
				$path = $this->locations[$name][0];
				$args = $this->locations[$name][1] ?? [];
				try {
					// include with bind
					$closure = call_user_func(
						function () use ($path, $args) {
							extract($args);

							return include $path;
						}
					);
				} catch (Throwable $e) {
					throw new Exception("CONTAINER_LOAD_FAILURE; Could not create $name, " . $e->getMessage(), previous: $e);
				}

				if (!($closure instanceof Closure)) {
					throw new Exception("CONTAINER_TYPE_FAILURE; Could not create $name, expected Closure not found");
				}
//TODO: Closure return type
				$this->register($name, $closure);
			}
		}

		if (isset($this->registry[$name])) {
			try {
				$instance = $this->registry[$name]($this);
			} catch (Exception $e) {
				throw new Exception(message: "CONTAINER_CREATE_ERR; could not create '$name', {$e->getMessage()}", previous: $e);
			}

			if (!is_object($instance)) {
				throw new Exception("CONTAINER_OBJECT_ERR; object not created for '$name'");
			}

			if ($store) {
				$this->instances[$name] = $instance;
			}

			return $instance;
		} else {
			try {
				return $this->instantiate($name, $name, $store);
			} catch (Exception $e) {
				throw new Exception(message: "CONTAINER_INSTANTIATE_ERR; {$e->getMessage()}", previous: $e);
			}
		}
	}

	/** 
	 * @param array<mixed> $args
	 * @throws Exception
	 */
	public function call(object $class, string $method_name, array $args = [], bool $store = false, bool $force_new = false, bool $store_reflection = false): mixed
	{
		$class_name = get_class($class);

		$reflection = $this->reflections[$class_name] ?? new ReflectionClass($class_name);

		try {
			$method = $reflection->getMethod($method_name);
			$params = $args ? $this->set_params($method->getParameters(), $args, $store, $force_new) : [];
		} catch (Exception $e) {
			throw new Exception(message: "CONTAINER_CALL_ERR; Could not call $class_name::$method_name, {$e->getMessage()}", previous: $e);
		}

// 		if (!is_array($params)) {
// 			throw new Exception(message: "CONTAINER_CALL_ERR; Could not call $class_name::$method_name");
// 		}

		if ($store_reflection) {
			$this->reflections[$class_name] = $reflection;
		}

		$callable = [$class, $method_name];
		if (!is_callable($callable)) {
			throw new Exception(message: "CONTAINER_CALL_ERR; Not callable $class_name::$method_name");
		}

		return call_user_func_array($callable, $params);
	}

	public function cache_get(string $name): bool
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

	/** @throws Exception */
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
	 * @throws Exception
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
				$params[$i] = $param->getDefaultValue();
				continue;
			}

			if ($p_null) {
				$params[$i] = null;
				continue;
			}

			$p_type = $param->getType();

			if (is_null($p_type)) {
				throw new Exception("cannot resolve parameter $p_name");
			}

			if ($p_type instanceof ReflectionUnionType) {
				throw new Exception("cannot handle union type parameter $p_name");
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
						//TODO realpath($this->locate_path . DIRECTORY_SEPARATOR deplicated
						or ($this->locate_path and realpath($this->locate_path . DIRECTORY_SEPARATOR . 'register.' . $name . '.php'))
					) {
						$params[$i] = $this->create($name, $store);
					} else {
						$class_name = $this->get_class_name_from_type($p_type_name);
						$params[$i] = $this->instantiate($class_name, $p_name, $store);
					}
				}
				continue;
			}

			throw new Exception("cannot resolve parameter $p_name");
		}

		return $params;
	}

	/** @throws Exception */
	protected function instantiate(string $class_name, ?string $name = null, bool $store = false): object
	{
		if (!class_exists($class_name)) {
			throw new Exception(message: "Could not find $class_name");
		}

// 		try {
		$reflection = new ReflectionClass($class_name);
// 		} catch (Exception $e) {
// 			throw new Exception(message: "could not reflect $class_name, {$e->getMessage()}", previous: $e);
// 		}

		$constructor = $reflection->getConstructor();

		try {
			if (!is_null($constructor)) {
				$class = $reflection->newInstanceArgs(
					$this->set_params($constructor->getParameters())
				);
			} else {
				$class = $reflection->newInstanceArgs();
			}
		} catch (Exception $e) {
			throw new Exception(message: "could not instantiate '$class_name', {$e->getMessage()}", previous: $e);
		}

		if ($store) {
			$this->instances[$name ?: $class_name] = $class;
		}

		return $class;
	}
}
