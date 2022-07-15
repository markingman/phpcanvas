<?php

namespace PHPCanvas\Cache;

interface CacheInterface
{
	public function index(string $path, int $chars = 0): string;

	public function put(string|array $fp, mixed $cache, ?int $ttl = null): bool;

	public function get(string|array $fp, ?int $ttl = null): mixed;

	public function delete(string|array $fp): bool;

	public function test(string|array $fp, int $ttl = 0): bool;

	public function gc(?int $ttl = null, ?int $max = null): int;
}
