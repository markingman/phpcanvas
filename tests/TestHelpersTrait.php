<?php

trait PHPCanvasTestHelpersTrait
{
	protected static $tmpdir;

	protected static function tmpdir_make($dir_name)
	{
		static::$tmpdir = rtrim(sys_get_temp_dir(), '/') . '/' . $dir_name;

		if (file_exists(static::$tmpdir)) {
			static::tmpdir_remove();
		}

		if (!file_exists(static::$tmpdir)) {
			mkdir(static::$tmpdir);
		}
		
		return file_exists(static::$tmpdir);
	}

	protected static function tmpdir_remove($tmpdir = null)
	{
		$tmpdir = is_null($tmpdir) ? static::$tmpdir : $tmpdir;
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($tmpdir, \FilesystemIterator::SKIP_DOTS), 
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $filename => $fileInfo) {
			if ($fileInfo->isDir()) {
				rmdir($filename);
			} else {
				unlink($filename);
			}
		}

		rmdir($tmpdir);
	}
}