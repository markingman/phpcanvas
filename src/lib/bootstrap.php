<?php

// Generic bootstrap

$Container = require __DIR__ . '/load_container.php';

// if (file_exists(store_path / container)) {
// try {
//  $Container = file_exists(store_path / container ? unserialize(file_get_contents(store_path / container)) : $Container = new PHPCanvas\Container(store, locate path);
// catch(Exceptin e)
// } else {
//  unlink(store_path / container )
// exit(Error)
// }

if (!$Container->cache_get('Config')) {
	$config_paths = $config_paths ?? [__DIR__ . '/../config/config.php', __DIR__ . '/../../../../../app/config/config.php'];
	$config = $config ?? [];
	$Container->locate('Config', __DIR__ . '/register.Config.php', ['config_paths' => $config_paths, 'config' => $config]);
}

foreach ([
			 ['App', __DIR__ . '/register.App.php', false],
			 ['Dispatch', __DIR__ . '/register.Dispatch.php', false],
			 ['Errors', __DIR__ . '/register.Errors.php', false],
			 ['Links', __DIR__ . '/register.Links.php', false],
			 ['Log', __DIR__ . '/register.Log.php', false],
			 ['Request', __DIR__ . '/register.Request.php', false],
			 ['Response', __DIR__ . '/register.Response.php', false],
			 ['Router', __DIR__ . '/register.Router.php', true],
		 ] as $it) {
	if ($it[2] and !$Container->cache_get($it[0])) {
		$Container->locate($it[0], $it[1]);
	} else {
		$Container->locate($it[0], $it[1]);
	}
}

return $Container['App'];
