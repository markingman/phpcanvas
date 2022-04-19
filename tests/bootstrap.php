<?php

$dir = __DIR__;
require $dir . '/TestHelpersTrait.php';

while ($dir !== '/' and !is_file($dir . '/vendor/autoload.php')) {
	$dir = dirname($dir);
}

require $dir . '/vendor/autoload.php';
