<?php

namespace PHPCanvas\Routing;

use Closure;
use Exception;

class Router implements RouterInterface
{
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

	/** @var array<string, int> $iname */
	protected array $iname = [];
	/** @var array<int, Route> $routes */
	protected array $routes = [];
	/** @var array<string, int[]> $index */
	protected array $index = [];
	protected int $i = 0;
	protected string $action_default = 'default';

	public function __construct(string $action_default = 'default')
	{
		$this->action_default = $action_default;
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

	/**
	 * @param array<string, string> $route_vars
	 * @param array<string> $m
	 * @return array<string, string>
	 */
	public static function get_route_vars(array $route_vars, array $m): array
	{
		// public static so callback functions can use this too

		array_shift($m);

		return array_combine(array_keys($route_vars), $m);
	}

	/**
	 * @param ?array<string, string> $vars
	 * @param array<string>|string|null $method
	 * @throws Exception
	 */
	public function add_route(
		string $name,
		string $path,
		string $controller = '',
		?string $action = null,
		?Closure $callback = null,
		?string $index = null,
		?array $vars = null,
		string|array|null $method = null,
	): bool {
		$action = $action ?? $this->action_default;

		if (empty($path)) {
			throw new Exception('ROUTER_NO_PATH; No path set for route');
		}

		if (empty($controller) and !is_null($callback)) {
			throw new Exception('ROUTER_NO_CONTROLLER; No controller set for route');
		}

		// can set action like 'name' => ['controller' => 'MyController::action']

		if (str_contains($controller, '::')) {
			$parts = explode('::', $controller, 2);
			$controller = $parts[0];
			$action = $parts[1];
		}

		// 'any', e.g. 'foo/{var}' or 'foo' can be set like foo/{var}**

		$any = false;
		if (str_ends_with($path, '**')) {
			$path = substr($path, 0, -2);
			$any = true;
		}

		// normalise path

		$path = ltrim($path, '/');

		// get index (any override value or, if present, the first path fragment)

		$index = $index ?? '';
		if (empty($index)) {
			preg_match('~^([^/{]+/)~', $path, $m);

			if (count($m) === 2) {
				$index = substr($m[1], 0, -1);
			}
		}

		// get vars (all the {var} items and any explicitly set values)

		preg_match_all('~{([^}]+)}~', $path, $m);
		$vars = [];
		if (count($m) === 2) {
			$vars = array_fill_keys($m[1], '');
		}

		foreach ($vars as $k => $v) {
			unset($vars[$k]);
			$vars[(string)$k] = (string)$v;
		}

		// create URL regx (convert foo/{bar} notation to regx)

		$esc = '~';
		$route_esc = preg_replace('~\{[^}]+}~', PHP_EOL, $path);//make {markers} into EOL placeholder chars for preg_quote()
		if (!is_string($route_esc)) {
			throw new Exception('ROUTER_ADD_ROUTE; Cannot add route path');
		}
		$route_esc = preg_quote($route_esc, $esc);//ensure anything in /url/path is now preg escaped
		$route_esc = str_replace(PHP_EOL, '([^/]+)', $route_esc);//replace placeholder chars back to reqx
		if ($any) {
			$route_esc .= '.*';//TODO is this .* or just * ?
		}
		$regx = $esc . '^' . $route_esc . '$' . $esc;//make regx using $esc chars

		// create rewrite sprintf (so get link uses sprintf() rather than str_replace

		$sprintf = (string)preg_replace('~{([^}]+)}\**~', '%s', $path);
		if ($any) {
			$sprintf .= '%s';
		}

		// normalise method

// 		$params['method'] = (array)$params['method'];
// 		foreach ($params['method'] as $k => $v) {
// 			$params['method'][$k] = strtoupper($v);
// 		}
		$method = empty($method) ? [] : array_map('strtoupper', (array)$method);
		$methodi = 0;
		foreach (static::METHODS as $method_type => $method_val) {
			if (in_array($method_type, $method)) {
				$methodi += $method_val;
			}
		}

		// store as integer

		$i = $this->i++;
		$this->iname[$name] = $i;

		// route (each route leads to a controller from URL string)

		$this->routes[$i] = new Route(
			controller: $controller,
			vars: $vars,
			action: $action,
			method: $methodi,
			sprintf: $sprintf,
			name: $name,
			regx: $regx,
			callback: $callback,
		);

		// routes can be indexed (first path fragment)

// 		if ($index) {
		if (!isset($this->index[$index])) {
			$this->index[$index] = [];
		}

		$this->index[$index][] = $i;

// 		}

		return true;
	}

	public function delete_route(string $name): bool
	{
		if (!isset($this->iname[$name])) {
			return false;
		}

		$i = $this->iname[$name];
		unset($this->routes[$i], $this->iname[$name]);
		foreach ($this->index as $index) {
			foreach ($index as $k => $v) {
				if ($v === $i) {
					unset($this->index[$name][$k]);
				}
			}
		}

		return true;
	}

	/**
	 * @return array<string, array{
	 *     path : string,
	 *     controller ?: string,
	 *     action ?: string,
	 *     method ?: array<string>,
	 *     vars ?: array<string, string>,
	 *     callback ?: Closure
	 * }>
	 */
	public function get_routes(): array
	{
		$routes = [];

		foreach ($this->routes as $route) {
			$_route = [
				'path' => $route->regx,
			];

			$_route['controller'] = $route->controller;

			$_route['action'] = $route->action;

			if ($route->method !== 0) {
				$_route['method'] = $this->get_route_methods($route->method);
			}

			if (count($route->vars)) {
				$_route['vars'] = $route->vars;
			}

			if ($route->callback) {
				$_route['callback'] = $route->callback;
			}

			$routes[$route->name] = $_route;
		}

		return $routes;
	}

	/** @return array{iname: array<string, int>, routes: array<int, Route>, index: array<string, int[]>} */
	public function dump(): array
	{
		return [
			'iname' => $this->iname,
			'routes' => $this->routes,
			'index' => $this->index,
		];
	}

	/** @return false|array{controller: string, action: string, vars: array<string, string>}
	 * @throws Exception
	 */
	public function get_route(string $method, string $url): false|array
	{
		$url = trim($url, '/');
		$url_index = strstr($url . '/', '/', true);
		$tried = [];

		if (isset($this->index[$url_index])) {
			foreach ($this->index[$url_index] as $i) {
				if (preg_match($this->routes[$i]->regx, $url, $m)) {
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
			if (preg_match($this->routes[$i]->regx, $url, $m)) {
				if (($return = $this->parse_route($method, $this->routes[$i], $m, $url)) !== false) {
					return $return;
				}
			}
		}

		return false;
	}

	/**
	 * @param array<string, string> $vars
	 * @throws Exception
	 */
	public function get_rewrite(string $name, array $vars = []): string
	{
		if (isset($this->iname[$name])) {
			$i = $this->iname[$name];
			$route =& $this->routes[$i];

			if ($vars === []) {
				if ($route->vars === []) {
					return '/' . $route->sprintf;
				}

				return '/' . vsprintf($route->sprintf, $route->vars);
			}

			$query_vars = array_diff_key($vars, $route->vars);
			$vars = array_replace($route->vars, array_intersect_key($vars, $route->vars));
			$link = vsprintf($route->sprintf, $vars);

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

	/** @return array<string> */
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

	/**
	 * @param array<string> $m
	 * @return false|array{
	 * controller : string,
	 *  action : string,
	 *  vars : array<string, string>
	 * }
	 * @throws Exception
	 */
	protected function parse_route(string $method, Route $route, array $m, string $url): false|array
	{
		if (!is_null($route->callback)) {
			$ret = call_user_func_array($route->callback, [$method, $route, $m, $url]);

			if ($ret === false) {
				return false;
			}

			if (
				!is_array($ret)
				or count($ret) !== 3
				or !isset($ret[0]) or !is_string($ret[0])
				or !isset($ret[1]) or !is_string($ret[1])
				or !isset($ret[2]) or !is_array($ret[2])
			) {
				throw new Exception('ROUTER_INVALID_CALLBACK; callback returned invalid structure');
			}

			foreach ($ret[2] as $k => $v) {
				if (!is_string($k) or !is_string($v)) {
					throw new Exception('ROUTER_INVALID_CALLBACK; callback returned invalid structure');
				}
			}

			return ['controller' => $ret[0], 'action' => $ret[1], 'vars' => $ret[2]];
		}

		if (empty($route->controller)) {
			return false;
		}

		if (!$this->is_route_method($method, $route->method)) {
			return false;
		}

		return ['controller' => $route->controller, 'action' => $route->action, 'vars' => $this->get_route_vars($route->vars, $m)];
	}
}
