<?php

namespace PHPCanvas\Routing;

interface LinksInterface
{
	/** @param array<string, string> $vars */
	public function get_link(string $name, array $vars = [], bool $relative = true, string $pcol = null): string;
}
