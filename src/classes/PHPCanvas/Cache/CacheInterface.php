<?php

namespace PHPCanvas\Cache;

interface CacheInterface
{
	public function index(string $path, int $chars = 0): string;

	public function put(string|array $fp, /*?mixed string|seliaiaable*/ $cache): bool;

	public function get(string|array $fp, int|bool $ttl = false): mixed;

	public function delete(string|array $fp): bool;

	public function test(string|array $fp, int|bool $ttl = false): bool;
}
