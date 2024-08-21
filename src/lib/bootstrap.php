<?php

// Generic bootstrap

$Container = require __DIR__ . '/load_container.php';

if (!$Container->get_cache('Config')) {
	$config_paths = $config_paths ?? [__DIR__ . '/../config/config.php', __DIR__ . '/../../../../../app/config/config.php'];
	$config = $config ?? [];
	$Container->locate('Config', __DIR__ . '/register.Config.php', ['config_paths' => $config_paths, 'config' => $config]);
}

foreach ([
			 ['Finder', __DIR__ . '/register.Finder.php', true],
			 ['Router', __DIR__ . '/register.Router.php', true],
			 ['Request', __DIR__ . '/register.Request.php', false],
			 ['Log', __DIR__ . '/register.Log.php', false],
			 ['Cache', __DIR__ . '/register.Cache.php', false],
			 ['Scope', __DIR__ . '/register.Scope.php', false],
			 ['Response', __DIR__ . '/register.Response.php', false],
			 ['Mail', __DIR__ . '/register.Mail.php', false],
			 ['Errors', __DIR__ . '/register.Errors.php', false],
			 ['Links', __DIR__ . '/register.Links.php', false],
			 ['Dispatch', __DIR__ . '/register.Dispatch.php', false],
			 ['ViewFinder', __DIR__ . '/register.ViewFinder.php', true],
			 ['View', __DIR__ . '/register.View.php', false],
			 ['App', __DIR__ . '/register.App.php', false],
		 ] as $it) {
	if ($it[2] and !$Container->get_cache($it[0])) {
		$Container->locate($it[0], $it[1]);
	} else {
		$Container->locate($it[0], $it[1]);
	}
}

return $Container['App'];