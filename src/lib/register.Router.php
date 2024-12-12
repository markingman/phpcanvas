<?php

return function (PHPCanvas\ContainerInterface $c) {
	$Router = new PHPCanvas\Routing\Router($c['Config']->ACTION_DEFAULT);

	$routes = include($c['Config']->ROUTES);

	if (is_array($routes)) {
		foreach ($routes as $name => $route) {
			if (is_string($name) and is_array($route)) {
				$Router->add_route($name, $route);
			}
		}
	}

	$c->cache_put('Router', $Router);

	return $Router;
};
	
