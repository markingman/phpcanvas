<?php

$config_paths = $config_paths ?? [];
$config = $config ?? [];

return function (PHPCanvas\ContainerInterface $c) use ($config_paths, $config): PHPCanvas\Config {
	if (!is_array($config_paths)) {
		throw new RunTimeException('Config expected config_paths array');
	}

	if (!is_array($config)) {
		throw new RunTimeException('Config expected config array');
	}

	$Config = new PHPCanvas\Config($config_paths, $config);
	$c->cache_put('Config', $Config);

	return $Config;
};
