<?php declare(strict_types=1);

namespace PHPCanvas;

use Closure;

interface ContainerInterface
{
	public function set_registrations_path(string $path): void;

	public function get_registrations_path(): string;

	public function set_alias(string $alias_name, string $target_name): void;

	public function get_alias(string $alias_name): ?string;

	/** @param ?array<mixed> $args */
	public function register_path(string $name, string $path, ?array $args = null): void;

	public function register(string $name, Closure $closure): void;

	public function create(string $name, bool $store = false): mixed;

	/** @param array<mixed> $args */
	public function call(object $class, string $method_name, array $args = [], bool $store = false, bool $force_new = false, bool $store_reflection = false): mixed;

	public function get(string $name, bool $store_created = true): mixed;

	public function set(string $name, object $value): void;

	public function exists(string $name): bool;

	public function unset(string $name): void;

	/** @return array<string, array{0: string, 1?: array<mixed>}> */
	public function list_registrations(): array;

	/** @return array<string, Closure> */
	public function list_registry(): array;

	/** @return array<string, object> */
	public function list_instances(): array;
}
