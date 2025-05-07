<?php

namespace PHPCanvas\Routing;

readonly class RouteMatch
{
	/** @param array<string, string> $vars */
	public function __construct(
		public string $controller,
		public string $action,
		public array $vars,
	) {
	}
}
