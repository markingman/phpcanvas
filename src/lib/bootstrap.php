<?php

// Generic bootstrap

$Container = require __DIR__ . '/load_container.php';

if (!$Container->get_cache('Config')) {
	$config_paths = @$config_paths ?: [__DIR__ . '/../config/config.php', __DIR__ . '/../../../../../app/config/config.php'];
	$config = @$config ?: [];
	$Container->locate('Config', __DIR__ . '/register.Config.php', ['config_paths' => $config_paths, 'config' => $config]);
}

if (!$Container->get_cache('Finder')) {
	$Container->locate('Finder', __DIR__ . '/register.Finder.php');
}

if (!$Container->get_cache('Router')) {
	$Container->locate('Router', __DIR__ . '/register.Router.php');
}

$Container->locate('Request', __DIR__ . '/register.Request.php');
$Container->locate('Log', __DIR__ . '/register.Log.php');
$Container->locate('Cache', __DIR__ . '/register.Cache.php');
$Container->locate('Scope', __DIR__ . '/register.Scope.php');
$Container->locate('Response', __DIR__ . '/register.Response.php');
$Container->locate('Mail', __DIR__ . '/register.Mail.php');
$Container->locate('Errors', __DIR__ . '/register.Errors.php');
$Container->locate('Links', __DIR__ . '/register.Links.php');
$Container->locate('Dispatch', __DIR__ . '/register.Dispatch.php');

if (!$Container->get_cache('ViewFinder')) {
	$Container->locate('ViewFinder', __DIR__ . '/register.ViewFinder.php');
}

$Container->locate('View', __DIR__ . '/register.View.php');
$Container->locate('App', __DIR__ . '/register.App.php');

return $Container['App'];