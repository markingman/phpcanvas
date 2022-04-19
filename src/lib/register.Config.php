<?php // $Id: register.Config.php 720 2017-09-07 12:56:50Z dev $

return function ($c) use ($config_paths, $config) {
	$Config = new PHPCanvas\Config($config_paths, $config);
	$c->cache('Config', $Config);

	return $Config;
};
