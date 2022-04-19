<?php

namespace PHPCanvas;

interface FinderInterface
{
	public function set_dirs(array $dirs);

	public function get_dir(string $dir);

	public function get_dirs();

	public function get(string $path, $dir = null, $cache = true);

	public function glob(string $glob, $dir = null);
}
