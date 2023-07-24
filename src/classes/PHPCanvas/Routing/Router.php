<?php

namespace PHPCanvas\Routing;

use Exception;

class Router implements RouterInterface
{
	const CONTROLLER = 0;
	const VARS = 1;
	const ACTION = 2;
	const METHOD = 4;
	const REGX = 5;
	const SPRNTF = 7;
	const NAME = 8;
	const CALLBACK = 10;

	const METHODS = [
		'GET' => 1,
		'HEAD' => 2,
		'POST' => 4,
		'PUT' => 8,
		'DELETE' => 16,
		'CONNECT' => 32,
		'OPTIONS' => 128,
		'TRACE' => 256,
		'PATCH' => 512,
	];

	protected array $iname = [];
	protected array $routes = [];
	protected array $index = [];
	protected int $i = 0;
	protected string $action_default = 'default';

	public function __construct(string $action_default = 'default')
	{
		$this->action_default = $action_default;
	}

	public function add_route(string $name, array $params): bool
	{
		if (!isset($params['path'])) {
			throw new Exception('ROUTER_NO_PATH; No path set for route');
		}

		if (!isset($params['controller'])) {
			if (!isset($params['callback'])) {
				throw new Exception('ROUTER_NO_CONTROLLER; No controller set for route');
			} else {
				$params['controller'] = '';
			}
		}

		$params = array_merge(
			[
				'action' => $this->action_default,
				'method' => 0,
				'name' => '',
			],
			$params
		);

		// can set action like 'path' => 'MyController::action'

		if (str_contains($params['controller'], '::')) {
			$parts = explode('::', $params['controller'], 2);
			$params['controller'] = $parts[0];
			$params['action'] = $parts[1];
		}

		// 'any', e.g. 'foo/{var}' or 'foo' can be set like foo/{var}**

		$any = false;
		if (str_ends_with($params['path'], '**')) {
			$params['path'] = substr($params['path'], 0, -2);
			$any = true;
		}

		// get index (any override value or, if present, the first path fragment)

		$index = '';
		if (isset($params['index'])) {
			$index = $params['index'];
		} else {
			$params['path'] = ltrim($params['path'], '/');
			preg_match('~^([^/{]+/)~', $params['path'], $m);

			if (count($m) === 2 and strlen($m[1])) {
				$index = substr($m[1], 0, -1);
			}
		}

		// get vars (all the {var} items and any explicitly set values)

		preg_match_all('~{([^}]+)}~', $params['path'], $m);
		$vars = [];
		if (count($m) === 2) {
			$vars = array_fill_keys($m[1], '');
		}

		if (isset($params['vars'])) {
			foreach ($params['vars'] as $k => $v) {
				$vars[$k] = $v;
			}
		}

		// create URL regx (convert foo/{bar} notation to regx)

		$esc = '~';
		$route_esc = preg_replace('~\{[^}]+}~', PHP_EOL, $params['path']);//make {markers} into EOL placeholder chars for preg_quote()
		$route_esc = preg_quote($route_esc, $esc);//ensure anything in /url/path is now preg escaped
		$route_esc = str_replace(PHP_EOL, '([^/]+)', $route_esc);//replace placeholder chars back to reqx
		if ($any) {
			$route_esc .= '.*';//TODO is this .* or just * ?
		}
		$regx = $esc . '^' . $route_esc . '$' . $esc;//make regx using $esc chars

		// create rewrite sprintf (so get link uses sprintf() rather than str_replace

		$sprintf = preg_replace('~{([^}]+)}\**~', '%s', $params['path']);
		if ($any) {
			$sprintf .= '%s';
		}

		// normalise method

		$params['method'] = (is_string($params['method']) and !strlen($params['method'])) ? [] : array_map('strtoupper', (array)$params['method']);
		$method = 0;
		foreach (static::METHODS as $method_type => $method_val) {
			if (in_array($method_type, $params['method'])) {
				$method += $method_val;
			}
		}

		// store as integer

		$i = $this->i++;
		$this->iname[$name] = $i;

		// route (each route leads to a controller from URL string)

		$route = [];
		if ($params['controller']) {
			$route[static::CONTROLLER] = $params['controller'];
		}
		$route[static::VARS] = $vars;
		$route[static::ACTION] = $params['action'];
		$route[static::METHOD] = $method;
		$route[static::SPRNTF] = $sprintf;
		$route[static::NAME] = $name;
		if (isset($params['callback'])) {
			$route[static::CALLBACK] = $params['callback'];
		}
		$route[static::REGX] = $regx;

		$this->routes[$i] = $route;

		// routes can be indexed (first path fragment) 

		if ($index) {
			if (!isset($this->index[$index])) {
				$this->index[$index] = [];
			}

			$this->index[$index][] = $i;
		}

		return true;
	}

	public function delete_route($name): bool
	{
		if (!isset($this->iname[$name])) {
			return false;
		}

		$i = $this->iname[$name];
		unset($this->routes[$i], $this->index[$i], $this->iname[$name]);

		return true;
	}

	public function get_routes(): array
	{
		$routes = [];

		foreach ($this->routes as $route) {
			$_route = [
				'path' => $route[static::REGX],
			];

			if (isset($route[static::CONTROLLER])) {
				$_route['controller'] = $route[static::CONTROLLER];
			}

			if (isset($route[static::ACTION])) {
				$_route['action'] = $route[static::ACTION];
			}

			if (isset($route[static::METHOD]) and $route[static::METHOD] !== 0) {
				$_route['method'] = $this->get_route_methods($route[static::METHOD]);
			}

			if (count($route[static::VARS])) {
				$_route['vars'] = $route[static::VARS];
			}

			if (isset($route[static::CALLBACK])) {
				$_route['callback'] = $route[static::CALLBACK];
			}

			$routes[$route[static::NAME]] = $_route;
		}

		return $routes;
	}

	public function dump(): array
	{
		return [
			'iname' => $this->iname,
			'routes' => $this->routes,
			'index' => $this->index,
		];
	}

	public function get_route($method, $url): false|array
	{
		$url = trim($url, '/');
		$url_index = strstr($url . '/', '/', true);
		$tried = [];

		if (isset($this->index[$url_index])) {
			foreach ($this->index[$url_index] as $i) {
				if (preg_match($this->routes[$i][static::REGX], $url, $m)) {
					if (($return = $this->parse_route($method, $this->routes[$i], $m, $url)) !== false) {
						return $return;
					}
				}
				$tried[$i] = true;
			}
		}

		foreach ($this->iname as $i) {
			if (isset($tried[$i])) {
				continue;
			}
			if (preg_match($this->routes[$i][static::REGX], $url, $m)) {
				if (($return = $this->parse_route($method, $this->routes[$i], $m, $url)) !== false) {
					return $return;
				}
			}
		}

		return false;
	}

	protected function parse_route(string $method, array $route, $m, $url): false|array
	{
		if (isset($route[static::CALLBACK])) {
			if (is_callable($route[static::CALLBACK])) {
				$ret = call_user_func_array($route[static::CALLBACK], [$method, $route, $m, $url]);
				if ($ret === false) {
					return false;
				}
				[$controller, $action, $vars] = $ret;

				return [$controller, $action, $vars];
			} else {
				return false;
			}
		}

		if (!isset($route[static::CONTROLLER])) {
			return false;
		}

		if (!$this->is_route_method($method, $route[static::METHOD])) {
			return false;
		}

		$vars = $this->get_route_vars($route[static::VARS], $m);
		$controller = $route[static::CONTROLLER];
		$action = $route[static::ACTION];

		return [$controller, $action, $vars];
	}

	public function get_rewrite($name, $vars = []): string
	{
		if (isset($this->iname[$name])) {
			$i = $this->iname[$name];
			$route =& $this->routes[$i];

			if ($vars === []) {
				if ($route[static::VARS] === []) {
					return '/' . $route[static::SPRNTF];
				}

				return '/' . vsprintf($route[static::SPRNTF], $route[static::VARS]);
			}

			$query_vars = array_diff_key($vars, $route[static::VARS]);
			$vars = array_replace($route[static::VARS], array_intersect_key($vars, $route[static::VARS]));
			$link = vsprintf($route[static::SPRNTF], $vars);

			if ($query_vars) {
				$link .= '?' . http_build_query($query_vars, '', '&', PHP_QUERY_RFC3986);
			}

			return '/' . $link;
		} else {
			throw new Exception(sprintf('ROUTER_NO_ROUTE; No link named %s', $name));
		}
	}

	public function get_action_default(): string
	{
		return $this->action_default;
	}

	public static function is_route_method(string $method, int $route_method): bool
	{
		// public static so callback functions can use this too

		if ($route_method > 0) {
			if (isset(static::METHODS[$method])) {
				if (!(static::METHODS[$method] & $route_method)) {
					return false;
				}
			}
		}

		return true;
	}

	public function get_route_methods(int $route_method): array
	{
		$methods = [];

		foreach (static::METHODS as $k => $v) {
			if (static::METHODS[$k] & $route_method) {
				$methods[] = $k;
			}
		}

		return $methods;
	}

	public static function get_route_vars(array $route_vars, array $m): array
	{
		// public static so callback functions can use this too

		array_shift($m);

		return array_combine(array_keys($route_vars), $m);
	}
}
