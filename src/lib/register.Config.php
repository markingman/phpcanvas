<?php

$config_paths = $config_paths ?? [];
$config = $config ?? [];

return function (PHPCanvas\ContainerInterface $c) use ($config_paths, $config) {
	$Config = new PHPCanvas\Config($config_paths, $config);
	$c->cache_put('Config', $Config);

	return $Config;
};
