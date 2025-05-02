<?php

namespace PHPCanvas\Routing;

use Closure;

class Route
{
	/** @param array<string, string> $vars */
	public function __construct(
		public readonly string $controller,
		public readonly array $vars,
		public readonly string $action,
		public readonly int $method,
		public readonly string $sprintf,
		public readonly string $name,
		public readonly string $regx,
		public readonly ?Closure $callback = null
	) {
	}
}
