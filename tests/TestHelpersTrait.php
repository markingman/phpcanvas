<?php

trait PHPCanvasTestHelpersTrait
{
	protected static $tmpdir;

	protected static function tmpdir_make($dir_name): bool
	{
		static::$tmpdir = rtrim(sys_get_temp_dir(), '/') . '/' . md5(random_bytes(10)) . '/' . $dir_name;

    	mkdir(static::$tmpdir, 0755, true);

		return file_exists(static::$tmpdir);
	}

	protected static function tmpdir_remove(): void
	{
		if (is_dir(static::$tmpdir)) {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(static::$tmpdir, FilesystemIterator::SKIP_DOTS), 
				RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ($iterator as $file => $info) {
				if ($info->isDir()) {
					rmdir($file);
				} else {
					unlink($file);
				}
			}

			rmdir(static::$tmpdir);
		}
	}
}