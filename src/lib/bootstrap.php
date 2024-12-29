<?php

// Generic bootstrap (copy and create new as required)

$dir = (string)($dir ?? __DIR__);
$container_store = (string)($container_store ?? '');
$locate_path = (string)($locate_path ?? '');
$config_paths = (array)($config_paths ?? [$dir . '/../config/config.php', $dir . '/../../../../../app/config/config.php']);
$config = (array)($config ?? []);

try {
	$Container = new PHPCanvas\Container($container_store, $locate_path);
} catch (Exception $e) {
	throw new Exception('Could not load Container');
}

if (!$Container->cache_get('Config')) {
	$Container->locate('Config', $dir . '/register.Config.php', ['config_paths' => $config_paths, 'config' => $config]);
}

foreach ([
			 ['App', $dir . '/register.App.php', false],
			 ['Dispatch', $dir . '/register.Dispatch.php', false],
			 ['Errors', $dir . '/register.Errors.php', false],
			 ['Links', $dir . '/register.Links.php', false],
			 ['Log', $dir . '/register.Log.php', false],
			 ['Request', $dir . '/register.Request.php', false],
			 ['Response', $dir . '/register.Response.php', false],
			 ['Router', $dir . '/register.Router.php', true],
		 ] as $it) {
	if ($it[2] and !$Container->cache_get($it[0])) {
		$Container->locate($it[0], $it[1]);
	} else {
		$Container->locate($it[0], $it[1]);
	}
}

if (!(($App = $Container->get('App')) instanceof PHPCanvas\Application)) {
	throw new Exception('Could not load App');
}

return $App;
