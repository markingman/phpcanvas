<?php

namespace PHPCanvas;

class Finder implements FinderInterface
{
	protected $dirs = [];//serialize class and save this

	public function __construct(array $dirs = [])
	{
		if ($dirs) {
			$this->set_dirs($dirs);
		}
	}

	public function set_dirs(array $dirs): bool
	{
		//[vendor/project => /path/to/dir, ...]

		foreach ($dirs as $k => $v) {
			if (!preg_match('/^[a-z0-9_-]+\/[a-z0-9_-]+$/i', $k)) {
				continue;
			}

			if (!$v = realpath($v) or !is_dir($v)) {
				continue;
			}

			$this->dirs[$k] = $v;
		}

		return true;
	}

	public function get_dir(string $dir): ?string
	{
		return @$this->dirs[$dir] ?: null;
	}

	public function get_dirs()
	{
		return $this->dirs;
	}

	public function get(string $path, ?string $dir = null, bool $cache = true): ?string
	{
		static $paths = [];

		if ($cache) {
			$cachep = ($dir ? $dir . ':' : '') . $path;
			if (isset($paths[$cachep])) {
				return $paths[$cachep];
			}
		}

		$file = null;

		if ($dir) {

			if (isset($this->dirs[$dir]) and file_exists($this->dirs[$dir] . '/' . $path)) {
				$file = $this->dirs[$dir] . '/' . $path;
			}

		} else {

			foreach ($this->dirs as $dir) {
				if (file_exists($dir . '/' . $path)) {
					$file = $dir . '/' . $path;
					break;
				}
			}

		}

		if ($cache) {
			$paths[$cachep] = $file;
		}

		return $file;
	}

	public function glob(string $glob, mixed $dir = null): array
	{
		$files = [];

		if (is_string($dir)) {

			if (isset($this->dirs[$dir])) {
				$len = strlen($this->dirs[$dir]) + 1;
				foreach (glob($this->dirs[$dir] . '/' . $glob) as $file) {
					$files[substr($file, $len)] = $file;
				}
			}

		} elseif (is_array($dir)) {

			foreach (array_intersect(array_keys($this->dirs), $dir) as $dir) {
				$len = strlen($this->dirs[$dir]) + 1;
				foreach (glob($this->dirs[$dir] . '/' . $glob) as $file) {
					$files[substr($file, $len)] = $file;
				}
			}

		} else {

			foreach ($this->dirs as $dir) {
				$len = strlen($dir) + 1;
				foreach (glob($dir . '/' . $glob) as $file) {
					$files[substr($file, $len)] = $file;
				}
			}

		}

		return $files;
	}
}
