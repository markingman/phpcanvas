<?php

function phpcanvas(
	?string $container_store = null, 
	?array $config_paths = null, 
	?array $config = null): PHPCanvas\Appplication
{
	return require __DIR__ . '/lib/bootstrap.php';
}
