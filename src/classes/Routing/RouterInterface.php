<?php

namespace PHPCanvas\Routing;

use Closure;

interface RouterInterface
{
	public static function is_route_method(string $method, int $route_method): bool;

	/**
	 * @param array<string, string> $route_vars
	 * @param array<int, array<string>> $m
	 * @return array<string, string>
	 */
	public static function get_route_vars(array $route_vars, array $m): array;

	/**
	 * @param ?array<string, string> $vars
	 * @param array<string>|string|null $method
	 */
	public function add_route(
		string $name,
		string $path,
		string $controller = '',
		?string $action = null,
		?Closure $callback = null,
		?string $index = null,
		?array $vars = null,
		string|array|null $method = null,
	): bool;

	public function delete_route(string $name): bool;

	/**
	 * @return array<string, array{
	 *     path : string,
	 *     controller ?: string,
	 *     action ?: string,
	 *     method ?: array<string>,
	 *     vars ?: array<string, string>,
	 *     callback ?: Closure
	 * }>
	 */
	public function get_routes(): array;

	/** @return array{iname: array<string, int>, routes: array<int, Route>, index: array<string, int[]>} */
	public function dump(): array;

	/** @return false|array{controller: string, action: string, vars: array<string, string>} */
	public function get_route(string $method, string $url): false|array;

	/** @param array<string, string> $vars */
	public function get_rewrite(string $name, array $vars = []): string;

	public function get_action_default(): string;
}
