<?php

namespace PHPCanvas;

class Config implements ConfigInterface
{
	/** @var array<string, string> @config */
	protected array $config = [];

	/**
	 * @param array<string> $paths
	 * @param ?array<string, string> $config
	 */
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

		if ($paths) {
			if (is_array($config = call_user_func_array('array_replace_recursive', $paths))) {
				foreach ($config as $k => $v) {
					if (is_string($k) and is_string($v)) {
						$this->config[$k] = $v;
					}
				}
			}
		}
	}

	public function __get(string $k): ?string
	{
		return $this->config[$k] ?? null;
	}

	public function __set(string $k, string $v): void
	{
		$this->config[$k] = $v;
	}

	public function __unset(string $k): void
	{
		unset($this->config[$k]);
	}

	/** @return array<string, string> */
	public function list(): array
	{
		return $this->config;
	}
}
