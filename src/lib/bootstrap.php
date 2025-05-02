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

if (!$Container->cache_get('Config', PHPCanvas\Config::class)) {
	$Container->locate('Config', $dir . '/Config.php', ['config_paths' => $config_paths, 'config' => $config]);
}

foreach ([
			 ['App', $dir . '/App.php', false],
			 ['Dispatch', $dir . '/Dispatch.php', false],
			 ['Errors', $dir . '/Errors.php', false],
			 ['Links', $dir . '/Links.php', false],
			 ['Log', $dir . '/Log.php', false],
			 ['Request', $dir . '/Request.php', false],
			 ['Response', $dir . '/Response.php', false],
			 ['Router', $dir . '/Router.php', true],
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
