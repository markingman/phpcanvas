<?php

if (false === function_exists('phpcanvas')) {

	function phpcanvas(
		?string $container_store = null, 
		?array $config_paths = null, 
		?array $config = null,
		?string $locate_path = null,
		): PHPCanvas\Application
	{
		return require __DIR__ . '/lib/bootstrap.php';
	}

}