<?php

namespace PHPCanvas\Test\App\View\Templates;

use RuntimeException;

abstract class AbstractTemplate
{
	public static function path_css(string $file = ''): string
	{
		return $this->path('css', $file);
	}

	public static function path_js(string $file = ''): string
	{
		return $this->path('js', $file);
	}

	public static function path_img(string $file = ''): string
	{
		return $this->path('img', $file);
	}

	public static function ob_start(): void
	{
		ob_start();
	}

	public static function ob_get_clean(): string
	{
		if (!$ob = ob_get_clean()) {
			throw new RuntimeException('Could not get clean obout buffer');
		}

		return $ob;
	}

	private static function path_asset(string $path, string $file): string
	{
		return '/' . $path . ($file? '/' . htmlentities($file) : '');
	}
}
