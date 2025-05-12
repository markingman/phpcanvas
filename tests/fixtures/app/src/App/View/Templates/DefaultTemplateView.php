<?php

namespace PHPCanvas\Test\App\View\Templates;

class DefaultTemplate
{
	public static function path_css(string $file = ''): string
	{
		return '/css' . ($file? '/' . htmlentities($file) : '');
	}

	public const string PATH_IMG = '/img';
	public const string PATH_JS = '/js';

	public static function html(
		string $title,
		string $content
	): string
	{
		ob_start();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?= htmlspecialchars($title) ?></title>
		<link href="<?= static::path_css('/main.css') ?>" rel="" type="stylesheet">
	</head>
	<body><?= $conetnt ?></body>
</html>
<?php
		if (!$ret =ob_get_clean()) {
			throw RuntimeException('Could not render template');
		}

		return $ret;
	}
}
