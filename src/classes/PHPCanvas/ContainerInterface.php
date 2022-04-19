<?php

namespace PHPCanvas;

use Closure;

interface ContainerInterface
{
	public function get_cache($name);

	public function cache($name, $instance = null);

	public function register($name, Closure $closure);

	public function locate($name, $path);

	public function get($name);

	public function create($name, $store = false);
}
