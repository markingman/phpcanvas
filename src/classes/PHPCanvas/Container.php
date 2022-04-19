<?php

namespace PHPCanvas;

use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;

class Container implements ContainerInterface, ArrayAccess
{
	protected $store_path = false;
	protected $registry = [];
	protected $locations = [];
	protected $instances = [];
	protected $reflections = [];

	public function set_store($path): void
	{
		$this->store_path = $path;
	}

	public function get_store(): string
	{
		return $this->store_path;
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

	public function locate(string $name, $path, $args = null): void
	{
		$this->locations[$name] = $args ? [$path, $args] : $path;
	}

	public function locate_if_not_exists(string $name, $path, $args = null): void
	{
		if (!$this->exists($name)) {
			$this->locations[$name] = $args ? [$path, $args] : $path;
		}
	}

	public function locates(array $locations): void
	{
		foreach ($locations as $location) {
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

	public function instanciate($class_name, $name = null, $args = [], $store_reflection = false)
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

	public function call($class, $method_name, $args = [], $force_new = false)
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

	public function create($name, $store = false)
	{
		if (!isset($this->registry[$name]) and !isset($this->locations[$name])) {
			try {
				return $this->instanciate($name);
			} catch (Exception $e) {
				throw new Exception("Class $name not in Container (" . $e->getMessage() . ')');
			}
		} elseif (!isset($this->registry[$name]) and isset($this->locations[$name])) {
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

	protected function set_params($params, $args = [], $force_new = false)
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

	public function get($name)
	{
		if (isset($this->instances[$name])) {
			return $this->instances[$name];
		} else {
			return $this->create($name, true);
		}
	}

	public function exists($name)
	{
		return isset($this->instances[$name]) or isset($this->locations[$name]) or isset($this->registry[$name]);
	}

	public function get_cache($name)
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

	public function cache($name, $instance = null)
	{
		if ($this->store_path) {
			if (!$instance and isset($this->instances[$name])) {
				$instance = $this->instances[$name];
			}

			if ($instance) {
				return @file_put_contents($this->store_path . '/' . $this->get_class_cache_name($name), serialize($instance));
			}
		}

		return false;
	}

	protected function get_class_cache_name($name)
	{
		return md5($name);
	}

	public function offsetSet($name, $value)
	{
		if (is_string($name) and is_object($value)) {
			if ($value instanceof Closure) {
				$this->register($name, $value);
			} else {
				$this->instances[$name] = $value;
			}
		} else {
			throw new Exception("Container can only register closures or set object instances");
		}

		return $this->instances[$name];
	}

	public function offsetExists($name)
	{
		return isset($this->instances[$name]);
	}

	public function offsetUnset($name)
	{
		unset($this->instances[$name]);
	}

	public function offsetGet($name)
	{
		return $this->get($name);
	}

	public function list_locations()
	{
		return $this->locations;
	}

	public function list_registry()
	{
		return $this->registry;
	}
}
