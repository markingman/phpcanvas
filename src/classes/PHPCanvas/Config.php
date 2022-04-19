<?php

namespace PHPCanvas;

class Config implements ConfigInterface
{
	protected $config = [];

	public function __construct(array $paths = [], ?array $config = null)
	{
		foreach ($paths as $n => $path) {
			$paths[$n] = call_user_func(
				function () use ($path) {
					return require $path;
				}
			);
		}

		if ($config) {
			$paths[] = $config;
		}

		$this->config = call_user_func_array('array_replace_recursive', $paths);
	}

	public function __get(string $k): string|bool|array|int|null
	{
		return @$this->config[$k];
	}

	public function __set(string $k, string|bool|array|int $v): void
	{
		$this->config[$k] = $v;
	}

	public function __unset(string $k): void
	{
		unset($this->config[$k]);
	}

	public function list(): array
	{
		return $this->config;
	}
}
