<?php

namespace PHPCanvas\Cache;

class Cache implements CacheInterface
{
	protected $dir;
	protected $prm = 0755;
	protected $ttl_default = 900;

	public function __construct(string $dir, $prm = null, ?int $ttl_default = null)
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

		if (strlen($path) > strlen($base)) {
			$path = dirname($path) . DIRECTORY_SEPARATOR;
		} else {
			$path = '';
		}

		if (is_numeric($base)) {
			return $path . floor((int)$base / pow(10, $chars)) . DIRECTORY_SEPARATOR . $base;
		} else {
			return $path . substr($base, 0, $chars) . DIRECTORY_SEPARATOR . $base;
		}
	}

	public function put(string|array $fp, /*?mixed string|seliaiaable*/ $cache): bool
	{
		if (is_array($fp)) {
			$fp = $this->index($fp[0], $fp[1]);
		}

		if (strpos($fp, DIRECTORY_SEPARATOR)) {
			$dir = dirname($fp);
			if (!file_exists($this->dir . DIRECTORY_SEPARATOR . $dir)) {
				mkdir($this->dir . DIRECTORY_SEPARATOR . $dir, $this->prm, true);
			} elseif (!is_dir($this->dir . DIRECTORY_SEPARATOR . $dir)) {
				trigger_error('Trying to write cache directory where file exists', E_USER_ERROR);
			}
		}

		$tmp = tempnam($this->dir, 'tmp');

		if (!is_string($cache)) {
			$cache = serialize($cache);
		}

		if (file_put_contents($tmp, $cache)) {
			return rename($tmp, $this->dir . DIRECTORY_SEPARATOR . $fp);
		} else {
			return false;
		}
	}

	public function get(string|array $fp, int|bool $ttl = false): mixed
	{
		if (is_array($fp)) {
			$fp = $this->index($fp[0], $fp[1]);
		}

		if ($this->test($fp, $ttl)) {
			$cache = file_get_contents($this->dir . DIRECTORY_SEPARATOR . $fp);

			if (strlen($cache) >= 4 and $cache[1] === ':' and (substr($cache, -1) === ';' or substr($cache, -1) === '}')) {
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
	}

	public function test(string|array $fp, int|bool $ttl = false): bool
	{
		if (is_array($fp)) {
			$fp = $this->index($fp[0], $fp[1]);
		}

		return (
			is_readable($this->dir . DIRECTORY_SEPARATOR . $fp)
			and ($ttl === true or filemtime($this->dir . DIRECTORY_SEPARATOR . $fp) > (time() - ($ttl !== false ? $ttl : $this->ttl_default)))
		);
	}
}
