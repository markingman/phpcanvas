<?php declare(strict_types=1);

namespace PHPCanvas;

interface ConfigInterface
{
	/**
	 * @param array<int, string> $paths
	 * @param ?array<string, string> $config
	 */
	public function __construct(array $paths = [], ?array $config = null);

	public function __get(string $k): ?string;

	public function __set(string $k, string $v): void;

	public function __unset(string $k): void;

	/** @return array<string, string> */
	public function list(): array;
}
