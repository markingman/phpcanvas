<?php

return function (PHPCanvas\ContainerInterface $c): PHPCanvas\Routing\Router {
	if (!($Config = $c->get('Config')) instanceof PHPCanvas\ConfigInterface) {
		throw new LogicException('Expected Container to have Config');
	}

	if (!isset($Config->ACTION_DEFAULT) or !isset($Config->ROUTES)) {
		throw new LogicException('Expected Config to have ACTION_DEFAULT and ROUTES');
	}

	if (!$routes_path = realpath($Config->ROUTES)) {
		throw new LogicException('Could not find ROUTES path');
	}

	$Router = new PHPCanvas\Routing\Router($Config->ACTION_DEFAULT);

	$routes = include($routes_path);

	if (is_array($routes)) {
		foreach ($routes as $name => $route) {
			if (is_string($name) and is_array($route)) {
				$Router->add_route(
					$name,
					$route['path'] ?? null,
					$route['controller'] ?? null,
					$route['action'] ?? null,
					$route['callback'] ?? null,
					$route['index'] ?? null,
					$route['vars'] ?? null,
					$route['method'] ?? null,
				);
			}
		}
	}

	$c->cache_put('Router', $Router);

	return $Router;
};
	
