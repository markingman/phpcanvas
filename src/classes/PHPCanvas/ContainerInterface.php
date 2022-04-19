<?php

namespace PHPCanvas;

use Closure;

interface ContainerInterface
{
	public function set_store($path): void;

	public function get_store(): string;

	public function register(string $name, Closure $closure): void;

	public function register_if_not_exists(string $name, Closure $closure): void;

	public function locate(string $name, $path, $args = null): void;

	public function locate_if_not_exists(string $name, $path, $args = null): void;

	public function locates(array $locations): void;

	public function locates_if_not_exists(array $locations): void;

	public function instanciate($class_name, $name = null, $args = [], $store_reflection = false);

	public function call($class, $method_name, $args = [], $force_new = false);

	public function create($name, $store = false);

	public function get($name);

	public function exists($name);

	public function get_cache($name);

	public function cache($name, $instance = null);
}
