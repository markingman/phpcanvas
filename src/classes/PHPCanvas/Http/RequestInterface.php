<?php

namespace PHPCanvas\Http;

interface RequestInterface
{
	public function get_method(): string;
	public function get_path(): string;
	public function set_get_value(string $k, string $v): void;
}
