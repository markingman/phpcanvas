<?php

namespace PHPCanvas;

use Closure;

interface ContainerInterface
{
	public function set_store(string $path): void;

	public function get_store(): string;

	public function set_locate_path(string $path): void;

	public function get_locate_path(): string;

	public function set_alias(string $alias_name, string $target_name): void;

	public function get_alias(string $alias_name): ?string;

	public function register(string $name, Closure $closure): void;

	public function create(string $name, bool $store = false): mixed;

	/** @param array<mixed> $args */
	public function call(object $class, string $method_name, array $args = [], bool $store = false, bool $force_new = false, bool $store_reflection = false): mixed;

	public function cache_get(string $name): bool;

	public function cache_put(string $name, mixed $instance = null): bool;

	public function cache_name(string $name): string;

	public function get(string $name, bool $store_created = true): mixed;

	public function set(string $name, object $value): void;

	public function exists(string $name): bool;

 	public function unset(string $name): void;

	/** @return array<string, array{0: string, 1?: array<mixed>}> */
	public function list_locations(): array;

	/** @return array<string, Closure> */
	public function list_registry(): array;

	/** @return array<string, object> */
	public function list_instances(): array;
}
