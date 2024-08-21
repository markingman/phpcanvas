<?php

namespace PHPCanvas;

use Closure;

interface ContainerInterface
{
	public function set_store(string $path): void;

	public function get_store(): string;

	public function set_locate_path(string $path): void;

	public function get_locate_path(): string;

	public function set_alias(string $alias_name, ?string $target_name): void;

	public function get_alias(string $alias_name): ?string;

	public function register(string $name, Closure $closure): void;

	public function create(string $name, bool $store = false): mixed;

	public function call(object $class, string $method_name, ?array $args = [], bool $store = false, bool $force_new = false, bool $store_reflection = false): mixed;

	public function cache_get(string $name): bool;

	public function cache_put(string $name, mixed $instance = null): bool;

	public function cache_name(string $name): string;

	public function get(string $name, $store_created = true): mixed;

	public function exists(string $name): bool;

	public function offsetSet(mixed $offset, mixed $value): void;

	public function offsetExists(mixed $offset): bool;

	public function offsetUnset(mixed $offset): void;

	public function offsetGet(mixed $offset): mixed;

	public function list_locations(): array;

	public function list_registry(): array;

	public function list_instances(): array;
}
