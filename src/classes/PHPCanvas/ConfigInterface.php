<?php

namespace PHPCanvas;

interface ConfigInterface
{
	public function __construct(array $paths = [], ?array $config = null);

	public function __get(string $k): string|bool|array|int|null;

	public function __set(string $k, string|bool|array|int $v): void;

	public function __unset(string $k): void;

	public function list(): array;
}
