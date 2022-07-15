<?php

namespace PHPCanvas;

use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;

class Container implements ContainerInterface, ArrayAccess
{
	protected string $store_path = '';
	protected string $locate_path = '';
	protected array $registry = [];
	protected array $locations = [];
	protected array $instances = [];
	protected array $reflections = [];

	public function set_store($path): void
	{
		$this->store_path = $path;
	}

	public function get_store(): string
	{
		return $this->store_path;
	}

	public function set_locate_path($path): void
	{
		$this->locate_path = $path;
	}

	public function get_locate_path(): string
	{
		return $this->locate_path;
	}

	public function register(string $name, Closure $closure): void
	{
		$this->registry[$name] = $closure;
	}

	public function register_if_not_exists(string $name, Closure $closure): void
	{
		if (!$this->exists($name)) {
			$this->registry[$name] = $closure;
		}
	}

	public function locate(string $name, string $path, $args = null): void
	{
		$this->locations[$name] = $args ? [$path, $args] : $path;
	}

	public function locate_if_not_exists(string $name, string $path, $args = null): void
	{
		if (!$this->exists($name)) {
			$this->locations[$name] = $args ? [$path, $args] : $path;
		}
	}

	public function locates(array $locations): void
	{
		foreach ($locations as $location) {
			if (isset($location[3])) {
				if ($this->get_cache($location[0])) {
					continue;
				}
			}

			$this->locate($location[0], $location[1], isset($location[2]) ? $location[2] : null);
		}
	}

	public function locates_if_not_exists(array $locations): void
	{
		foreach ($locations as $location) {
			if (!$this->exists($location[0])) {
				$this->locate($location[0], $location[1], isset($location[2]) ? $location[2] : null);
			}
		}
	}

	public function instanciate(string $class_name, ?string $name = null, array $args = [], bool $store_reflection = false): mixed
	{
		if (!is_null($name)) {
			if (isset($this->registry[$name]) or isset($this->locations[$name])) {
				return $this->get($name);
			}
		}

		$reflection = new ReflectionClass($class_name);
		$constructor = $reflection->getConstructor();

		if (!is_null($constructor)) {
			$params = $constructor->getParameters();
			$params = $this->set_params($params, $args);
			$class = $reflection->newInstanceArgs($params);
		} else {
			$class = $reflection->newInstanceArgs();
		}

		if (!is_null($name)) {
			$this->instances[$name] = $class;
		}

		if ($store_reflection) {
			$this->reflections[$class_name] = $reflection;
		}

		return $class;
	}

	public function call($class, $method_name, $args = [], $force_new = false): mixed
	{
		$class_name = get_class($class);

		if (!isset($this->reflections[$class_name])) {
			$this->reflections[$class_name] = new ReflectionClass($class_name);
		}

		$method = $this->reflections[$class_name]->getMethod($method_name);
		$params = $method->getParameters();
		$params = $this->set_params($params, $args, $force_new);

		return call_user_func_array([$class, $method_name], $params);
	}

	public function create(string $name, bool $store = false): mixed
	{
		if (!isset($this->registry[$name])) {
			if (!isset($this->locations[$name])) {
				if ($this->locate_path) {
					if (realpath($this->locate_path . '/register.' . $name . '.php')) {
						$this->locate($name, $this->locate_path . '/register.' . $name . '.php');
					}
				}
				
				if (!isset($this->locations[$name])) {
					try {
						return $this->instanciate($name);
					} catch (Exception $e) {
						throw new Exception("Class $name not in Container (" . $e->getMessage() . ')');
					}
				}
			}

			if (isset($this->locations[$name])) {
				if (is_array($this->locations[$name])) {
					$path = $this->locations[$name][0];
					$args = $this->locations[$name][1];
					$closure = call_user_func(
						function () use ($path, $args) {
							extract($args);

							return include $path;
						}
					);
				} else {
					$path = $this->locations[$name];
					$closure = call_user_func(
						function () use ($path) {
							return include $path;
						}
					);
				}

				if ($closure instanceof Closure) {
					$this->register($name, $closure);
				}
			}
		}

		if (isset($this->registry[$name])) {
			$class = $this->registry[$name];
			$instance = $class($this);
			if ($store) {
				$this->instances[$name] = $instance;
			}

			return $instance;
		} else {
			throw new Exception("Class $name could not be created (not in registry)");
		}
	}

	protected function set_params(array $params, array $args = [], bool $force_new = false): mixed
	{
		foreach ($params as $i => $param) {
			if (isset($args[$i])) {
				$params[$i] = $args[$i];
				continue;
// 			} elseif (isset($args[$param_name])) {
// 				$params[$i] = $args[$param_name];
// 				continue;
			}

			$param_name = $param->getName();
			$param_optional = $param->isOptional();
			$param_type = $param->getType();

			// @todo if is not !int !array etc, assume object
			if (!is_null($param_type)) {
				if (!$force_new and isset($this->instances[$param_name])) {
					$params[$i] = $this->instances[$param_name];
					continue;
				} elseif (!$param_optional) {
					$params[$i] = $this->instanciate($param_type->getName(), $param_name);
					continue;
				}
			}

			if ($param_optional) {
				$params[$i] = $param->getDefaultValue();
			} else {
				$params[$i] = null;
			}
		}

		return $params;
	}

	public function get(string $name): mixed
	{
		if (isset($this->instances[$name])) {
			return $this->instances[$name];
		} else {
			return $this->create($name, true);
		}
	}

	public function exists(string $name): bool
	{
		return isset($this->instances[$name]) or isset($this->locations[$name]) or isset($this->registry[$name]);
	}

	public function get_cache(string $name): bool
	{
		if (
			!$this->store_path
			or ($this->store_path and (!$class = @file_get_contents($this->store_path . '/' . $this->get_class_cache_name($name)) or !$class = @unserialize($class)))
		) {
			return false;
		} else {
			$this->instances[$name] = $class;

			return true;
		}
	}

	public function cache(string $name, mixed $instance = null): bool
	{
		if ($this->store_path) {
			if (!$instance and isset($this->instances[$name])) {
				$instance = $this->instances[$name];
			}

			if ($instance) {
				return (bool)@file_put_contents($this->store_path . '/' . $this->get_class_cache_name($name), serialize($instance));
			}
		}

		return false;
	}

	protected function get_class_cache_name(string $name): string
	{
		return md5($name);
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
			throw new Exception("Container can only register closures or set object instances");
		}
	}

	public function offsetExists($name): bool
	{
		return isset($this->instances[$name]);
	}

	public function offsetUnset($name): void
	{
		unset($this->instances[$name]);
	}

	public function offsetGet($name): mixed
	{
		return $this->get($name);
	}

	public function list_locations(): array
	{
		return $this->locations;
	}

	public function list_registry(): array
	{
		return $this->registry;
	}
}
