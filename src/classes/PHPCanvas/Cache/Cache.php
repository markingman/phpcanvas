<?php

namespace PHPCanvas\Cache;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Cache implements CacheInterface
{
	protected string $dir;
	protected int $prm = 0755;
	protected int $ttl_default = 900;

	public function __construct(string $dir, ?int $prm = null, ?int $ttl_default = null)
	{
		$this->dir = $dir;

		if (!is_null($prm)) {
			$this->prm = $prm;
		}

		if (!is_null($ttl_default)) {
			$this->ttl_default = $ttl_default;
		}
	}

	public function index(string $path, int $chars = 0): string
	{
		$base = basename($path);
		$path = strlen($path) > strlen($base) ? dirname($path) . DIRECTORY_SEPARATOR : '';

		if (is_numeric($base)) {
			return $path . floor((int)$base / pow(10, $chars)) . DIRECTORY_SEPARATOR . $base;
		} else {
			return $path . substr($base, 0, $chars) . DIRECTORY_SEPARATOR . $base;
		}
	}

	public function put(string|array $fp, mixed $cache, ?int $ttl = null): bool
	{
		if (is_array($fp)) {
			$fp = $this->index($fp[0], $fp[1]);
		}

		if (strpos($fp, DIRECTORY_SEPARATOR)) {
			$dir = dirname($fp);
			if (!file_exists($this->dir . DIRECTORY_SEPARATOR . $dir)) {
				mkdir($this->dir . DIRECTORY_SEPARATOR . $dir, $this->prm, true);
			} elseif (!is_dir($this->dir . DIRECTORY_SEPARATOR . $dir)) {
				throw new Exception(message: 'Trying to write cache directory where file exists');
			}
		}

		$tmp = tempnam($this->dir, 'tmp');

		if (!is_string($cache)) {
			$cache = serialize($cache);
		}

		if (is_null($ttl)) {
			$ttl = $this->ttl_default;
		}

		if (file_put_contents($tmp, $cache)) {
			touch($tmp, time() + $ttl);

			return rename($tmp, $this->dir . DIRECTORY_SEPARATOR . $fp);
		} else {
			return false;
		}
	}

	public function get(string|array $fp, ?int $ttl = null): mixed
	{
		if (is_array($fp)) {
			$fp = $this->index($fp[0], $fp[1]);
		}

		if (is_null($ttl)) {
			$ttl = 0;
		}

		if ($this->test($fp, $ttl)) {
			$cache = file_get_contents($this->dir . DIRECTORY_SEPARATOR . $fp);

			if (strlen($cache) >= 4 and $cache[1] === ':' and (str_ends_with($cache, ';') or str_ends_with($cache, '}'))) {
				return unserialize($cache);
			} else {
				return $cache;
			}
		} else {
			return '';
		}
	}

	public function delete(string|array $fp): bool
	{
		if (is_array($fp)) {
			$fp = $this->index($fp[0], $fp[1]);
		}

		if (is_dir($this->dir . DIRECTORY_SEPARATOR . $fp)) {
			foreach (glob($this->dir . DIRECTORY_SEPARATOR . $fp . '*', GLOB_MARK) as $file) {
				$this->delete($file);
			}

			return rmdir($this->dir . DIRECTORY_SEPARATOR . $fp);
		} elseif (is_file($this->dir . DIRECTORY_SEPARATOR . $fp)) {
			return unlink($this->dir . DIRECTORY_SEPARATOR . $fp);
		}

		return false;
	}

	public function test(string|array $fp, int $ttl = 0): bool
	{
		if (is_array($fp)) {
			$fp = $this->index($fp[0], $fp[1]);
		}

		return (
			is_readable($this->dir . DIRECTORY_SEPARATOR . $fp)
			and filemtime($this->dir . DIRECTORY_SEPARATOR . $fp) >= time() + $ttl
		);
	}

	public function gc(?int $ttl = null, ?int $max = null): int
	{
		if (is_null($ttl)) {
			$ttl = 0;
		}

		if (is_null($max)) {
			$max = PHP_INT_MAX;
		}

		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($this->dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		$n = 0;
		$t = time() + $ttl;

		$it->rewind();
		while ($it->valid()) {
			$n++;
			$f = $it->current();
			if (!$f->isDir()) {
				if ($f->getMtime() < $t) {
					unlink($f->getPathname());
				}
			} else {
				if (!$it->callHasChildren()) {
					rmdir($f->getPathname());
				}
			}
			if ($n >= $max) {
				break;
			}
			$it->next();
		}

		return $n;
	}
}
