<?php

namespace PHPCanvas\Routing;

interface RouterInterface
{
	public function add_route(string $name, array $params): bool;

	public function delete_route(string $name): bool;

	public function get_routes(): array;

	public function dump(): array;

	public function get_route(string $method, string $url): false|array;

	/** @param array<string, string> $vars */
	public function get_rewrite(string $name, array $vars = []): string;

	public function get_action_default(): string;

	public static function is_route_method(string $method, int $route_method): bool;

	public static function get_route_vars(array $route_vars, array $m): array;
}
