<?php

// Generic bootstrap

$Container = require __DIR__ . '/load_container.php';

if (!$Container->get_cache('Config')) {
	$config_paths = @$config_paths ?: [__DIR__ . '/../config/config.php', __DIR__ . '/../../../../../app/config/config.php'];
	$config = @$config ?: [];
	$Container->locate('Config', __DIR__ . '/register.Config.php', ['config_paths' => $config_paths, 'config' => $config]);
}

$Container->locates([
	['Finder', __DIR__ . '/register.Finder.php', null, true],
	['Router', __DIR__ . '/register.Router.php', null, true],
	['Request', __DIR__ . '/register.Request.php'],
	['Log', __DIR__ . '/register.Log.php'],
	['Cache', __DIR__ . '/register.Cache.php'],
	['Scope', __DIR__ . '/register.Scope.php'],
	['Response', __DIR__ . '/register.Response.php'],
	['Mail', __DIR__ . '/register.Mail.php'],
	['Errors', __DIR__ . '/register.Errors.php'],
	['Links', __DIR__ . '/register.Links.php'],
	['Dispatch', __DIR__ . '/register.Dispatch.php'],
	['ViewFinder', __DIR__ . '/register.ViewFinder.php', null, true],
	['View', __DIR__ . '/register.View.php'],
	['App', __DIR__ . '/register.App.php'],
]);

return $Container['App'];