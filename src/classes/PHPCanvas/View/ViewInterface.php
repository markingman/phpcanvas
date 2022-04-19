<?php

namespace PHPCanvas\View;

interface ViewInterface
{
	public function get_view(string $view, $search_path = null, bool $use_cache = true): string;

	public function get_link(string $name, array $vars = [], bool $relative = true): string;

	public function view(string $view, array $_VARS = null, $search_path = null, $cache = true): mixed;
}
