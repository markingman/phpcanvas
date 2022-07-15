<?php

return function ($c) use ($config_paths, $config) {
	$Config = new PHPCanvas\Config($config_paths, $config);
	$c->cache('Config', $Config);

	return $Config;
};
