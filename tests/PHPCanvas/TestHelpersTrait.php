<?php

namespace PHPCanvas;

trait TestHelpersTrait
{
	protected static ?string $tmpdir = null;

	protected static function tmpdir_make(): bool
	{
		try {
			static::$tmpdir = rtrim(sys_get_temp_dir(), '/') . '/' . bin2hex(random_bytes(4));
		} catch (Exception $e) {
			throw new Exception(message: 'Could not create tmp dir', previous: $e);
		}

		return mkdir(static::$tmpdir, 0755, true);
	}

	protected static function tmpdir_remove(): bool
	{
		if (is_null(static::$tmpdir)) {
			return false;
		}

		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(static::$tmpdir, FilesystemIterator::SKIP_DOTS), 
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($it as $file => $info) {
			$info->isDir() ? rmdir($file) : unlink($file);
		}

		return rmdir(static::$tmpdir);
	}
}