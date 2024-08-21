<?php

namespace PHPCanvas;

use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionUnionType;
use Throwable;

class Container implements ContainerInterface, ArrayAccess
{
	protected string $store_path = '';// serialized classes cache dir
	protected string $locate_path = '';// default registrations dir
	protected array $locations = [];// list of file paths to closures
	protected array $registry = [];// list of closures
	protected array $reflections = [];// cache of reflections
	protected array $instances = [];// list of instantiated objects
	protected array $aliases = [];// list of name aliases

	public function set_store(string $path): void
	{
		$this->store_path = $path;
	}

	public function get_store(): string
	{
		return $this->store_path;
	}

	public function set_locate_path(string $path): void
	{
		$this->locate_path = realpath($path) ?: '';
	}

	public function get_locate_path(): string
	{
		return $this->locate_path;
	}

	public function set_alias(string $alias_name, ?string $target_name): void
	{
		$this->aliases[$alias_name] = $target_name;
	}

	public function get_alias(string $alias_name): ?string
	{
		return $this->aliases[$alias_name] ?? null;
	}

	protected function get_class_name_from_type(string $name): string
	{
		if (str_ends_with($name, 'Interface') and ($len = strlen($name)) > 9) { // strlen('Interface') === 9
			$name = substr($name, 0, $len - 9);
		}

		return $name;
	}

	public function locate(string $name, string $path, ?array $args = null): void
	{
		$this->locations[$name] = $args ? [$path, $args] : [$path];
	}

	public function register(string $name, Closure $closure): void
	{
		$this->registry[$name] = $closure;
	}

	/**
	 * @throws Exception
	 */
	public function create(string $name, bool $store = false): mixed
	{
		if (!isset($this->registry[$name])) {
			if (!isset($this->locations[$name])) {
				if ($this->locate_path) {
					$locate_path = realpath($this->locate_path . DIRECTORY_SEPARATOR . 'register.' . $name . '.php');
					if ($locate_path and str_starts_with($locate_path, $this->locate_path)) {
						$this->locate($name, $this->locate_path . DIRECTORY_SEPARATOR . 'register.' . $name . '.php');
					}
				}
			}

			if (isset($this->locations[$name])) {
				$path = $this->locations[$name][0];
				$args = count($this->locations[$name]) > 1 ? $this->locations[$name][1] : [];
				try {
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
//TODO: Clocsure return type
				$this->register($name, $closure);
			}
		}

		if (isset($this->registry[$name])) {
			try {
				$instance = $this->registry[$name]($this);
			} catch (Exception $e) {
				throw new Exception("CONTAINER_CREATE_ERR; could not create '$name', {$e->getMessage()}");
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
				throw new Exception("CONTAINER_INSTANTIATE_ERR; {$e->getMessage()}");
			}
		}
	}

	public function call(object $class, string $method_name, ?array $args = [], bool $store = false, bool $force_new = false, bool $store_reflection = false): mixed
	{
		$class_name = get_class($class);

		$reflection = $this->reflections[$class_name] ?? new ReflectionClass($class_name);

		try {
			$method = $reflection->getMethod($method_name);
			$params = $this->set_params($method->getParameters(), $args, $store, $force_new);
		} catch (Exception $e) {
			throw new Exception("CONTAINER_CALL_ERR; Could not call $class_name::$method_name, {$e->getMessage()}");
		}

		if ($store_reflection) {
			$this->reflections[$class_name] = $reflection;
		}

		return call_user_func_array([$class, $method_name], $params);
	}

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

			if ($p_type instanceof ReflectionUnionType) {
				throw new Exception("cannot resolve paramter $p_name");
			}

			$p_type_name = $p_type->getName();
// if (str_ends_with($p_type_name, 'Interface'))prx($p_type_name);
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
					if (isset($this->registry[$name]) or isset($this->locations[$name])) {
						$params[$i] = $this->create($name, $store);
					} else {
						$class_name = $this->get_class_name_from_type($p_type_name);
						$params[$i] = $this->instantiate($class_name, $p_name, $store);
					}
				}
				continue;
			}

			throw new Exception("cannot resolve paramter $p_name");
		}

		return $params;
	}

	protected function instantiate(string $class_name, ?string $name = null, bool $store = false): mixed
	{
		try {
			$reflection = new ReflectionClass($class_name);
		} catch (Exception $e) {
			throw new Exception("could not reflect $class_name, {$e->getMessage()}");
		}

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
			throw new Exception("could not instantiate '$class_name', {$e->getMessage()}");
		}

		if (!is_null($store)) {
			$this->instances[$name ?: $class_name] = $class;
		}

		return $class;
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

	public function get(string $name, $store_created = true): mixed
	{
		$name = $this->aliases[$name] ?? $name;

		return $this->instances[$name] ?? $this->create($name, $store_created);
	}

	public function exists(string $name): bool
	{
		$name = $this->aliases[$name] ?? $name;

		return isset($this->instances[$name]) or isset($this->locations[$name]) or isset($this->registry[$name]);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (is_string($offset) and is_object($value)) {
			if ($value instanceof Closure) {
				$this->register($offset, $value);
			} else {
				$this->instances[$offset] = $value;
			}
		} else {
			throw new Exception("Could not set $offset", 100012);
		}
	}

	public function offsetExists(mixed $offset): bool
	{
		return isset($this->instances[$offset]);
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->instances[$offset]);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	public function list_locations(): array
	{
		return $this->locations;
	}

	public function list_registry(): array
	{
		return $this->registry;
	}

	public function list_instances(): array
	{
		return $this->instances;
	}
}
