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

	public function set_dirs(array $dirs)
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

	public function get_dir(string $dir)
	{
		return @$this->dirs[$dir] ?: null;
	}

	public function get_dirs()
	{
		return $this->dirs;
	}

	public function get(string $path, $dir = null, $cache = true)
	{
		static $paths = [];

		if ($cache and isset($paths[$path])) {
			return $paths[$path];
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
			$paths[$path] = $file;
		}

		return $file;
	}

	public function glob(string $glob, $dir = null)
	{
		if (is_string($dir)) {

			if (isset($this->dirs[$dir])) {
				return glob($this->dirs[$dir] . '/' . $glob);
			}

		} elseif (is_array($dir)) {

			$files = [];
			foreach (array_intersect($this->dirs, $dir) as $dir) {
				foreach (glob($dir . '/' . $glob) as $file) {
					$files[$file] = null;
				}
			}

			return array_keys($files);

		} else {

			$files = [];
			foreach ($this->dirs as $dir) {
				foreach (glob($dir . '/' . $glob) as $file) {
					$files[$file] = null;
				}
			}

			return array_keys($files);

		}
	}
}
