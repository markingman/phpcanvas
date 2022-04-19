<?php

return function ($c) {
	$Router = new PHPCanvas\Routing\Router($c['Config']->ACTION_DEFAULT);

	$routes = $c['Finder']->glob($c['Config']->ROUTES_GLOB, @$c['Config']->ROUTES_DIRS ?: null);

	foreach ($routes as $route) {
// 		try {
		foreach (include $route as $name => $params) {
			$Router->add_route($name, $params);
		}
	}

	$c->cache('Router', $Router);

	return $Router;
};
