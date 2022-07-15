<?php

namespace PHPCanvas\Routing;

use \Exception;

class Router implements RouterInterface
{
	const CONTROLLER = 0;
	const VARS = 1;
	const ACTION = 2;
	const ACTION_MAPS = 3;
	const METHOD = 4;
	const PRNTF = 7;
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

	protected $routes = [];
	protected $index = [];
	protected $regx = [];

	protected $action_default;

	public function __construct(string $action_default = 'default')
	{
		$this->action_default = $action_default;
	}

	public function add_route(string $name, array $params)
	{
		if (!isset($params['path'])) {
			throw new Exception('No path set for route');
		}

		if (!isset($params['controller'])) {
			if (!isset($params['callback'])) {
				throw new Exception('No controller set for route');
			} else {
				$params['controller'] = '';
			}
		}

		$params = array_merge(
			[
				'action' => $this->action_default,
// 				'action_maps' => [],
				'method' => 0,
				'name' => '',
			],
			$params
		);

		// can set action like 'path' => 'MyController::action'

		if (strpos($params['controller'], '::') !== false) {
			$parts = explode('::', $params['controller'], 2);
			$params['controller'] = $parts[0];
			$params['action'] = $parts[1];
		}

		// 'any', e.g. 'foo/{var}' or 'foo' can be set like foo/{var}**

		$any = false;
		if (substr($params['path'], -2) === '**') {
			$params['path'] = substr($params['path'], 0, -2);
			$any = true;
		}

		// add base route if set via '*' character (e.g. foo/{action}* creates 'foo/{action}' and 'foo')

		//@todo: change this so it's 'action_maps' '' => default_action (if not excplicty set)
// 		if (substr($params['path'], -1) === '*') {
// 			$params['path'] = substr($params['path'], 0, -1);
// 			$base_route = preg_replace('~/[^/\*]*$~', '', $params['path']);
// 			$base_params = $params;
// 			if (substr($params['path'], -9) === '/{action}') {//if we're removing action from URL, also need to remove action maps
// 				$base_params['action_maps'] = [];
// 			}
// 			$this->add_route($base_route, $base_params);
// 		}

		// get index (first path fragment, if present)

		$params['path'] = ltrim($params['path'], '/');
		preg_match('~^([^/{]+/)~', $params['path'], $m);

		$index = '';
		if (count($m) === 2 and strlen($m[1])) {
			$index = substr($m[1], 0, -1);
		}

		// get vars (all the {var} items)

		preg_match_all('~{([^}]+)}~', $params['path'], $m);
		$vars = [];
		if (count($m) === 2) {
			$vars = array_fill_keys($m[1], '');
		}

		// create URL regx (convert foo/{bar} notation to regx)

		$esc = '~';
		$route_esc = preg_replace('~\{[^\}]+\}~', PHP_EOL, $params['path']);//make {markers} into EOL placeholder chars for preg_quote()
		$route_esc = preg_quote($route_esc, $esc);//ensure anything in /url/path is now preg escaped
		$route_esc = str_replace(PHP_EOL, '([^/]+)', $route_esc);//replace placeholder chars back to reqx
		if ($any) {
			$route_esc .= '.*';
		}
		$regx = $esc . '^' . $route_esc . '$' . $esc;//make regx using $esc chars

		// create rewrite printf (so get link uses sprinf() rather than str_replace

		$printf = preg_replace('~{([^}]+)}\**~', '%s', $params['path']);
		if ($any) {
			$printf .= '%s';
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

		$cname = crc32($name);
		$route = [];
		if ($params['controller']) {
			$route[static::CONTROLLER] = $params['controller'];
		}
		$route[static::VARS] = $vars;
		$route[static::ACTION] = $params['action'];
		if (!empty($params['action_maps'])) {
			$route[static::ACTION_MAPS] = $params['action_maps'];
		}
		$route[static::METHOD] = $method;
		$route[static::PRNTF] = $printf;
		$route[static::NAME] = $name;
		if (isset($params['callback'])) {
			$route[static::CALLBACK] = $params['callback'];
		}
		$this->routes[$cname] = $route;

		// route (each route leads to a controller from URL string)

		$this->regx[$cname] = $regx;

		// routes can be indexed (first path fragment) 

		if ($index) {
			if (!isset($this->index[$index])) {
				$this->index[$index] = [];
			}

			$this->index[$index][] = $cname;
		}

		return true;
	}

	public function delete_route($name)
	{
// 		$cname = crc32($name);
// 		unset($this->routes[$cname]);
// 		$this->regx[$cname] = $regx;
// 		recursive index 
// 			$this->index[$index][] = $cname;
	}

	public function get_routes()
	{
		$routes = [];
		foreach ($this->routes as $cname => $route) {
			$_route = [
				'path' => $this->regx[$cname],
			];

			if (isset($route[static::CONTROLLER])) {
				$routes['action'] = $route[static::CONTROLLER];
			}

			if (isset($route[static::ACTION])) {
				$routes['action'] = $route[static::ACTION];
			}

			if (count($route[static::VARS])) {
				$_route['vars'] = $route[static::VARS];
			}

			if (isset($route[static::CALLBACK])) {
				$routes['callback'] = $route[static::CALLBACK];
			}

			$routes[$route[static::NAME]] = $_route;
		}

		return $routes;
	}

	public function dump()
	{
		return [
			'routes' => $this->routes,
			'index' => $this->index,
			'regx' => $this->regx,
		];
	}

	public function get_route($method, $url)
	{
		$url = trim($url, '/');
		$url_index = strstr($url . '/', '/', true);
		$tried = [];

		if (isset($this->index[$url_index])) {
			foreach ($this->index[$url_index] as $cname) {
				if (preg_match($this->regx[$cname], $url, $m)) {
					if (($return = $this->parse_route($method, $this->routes[$cname], $m, $url)) !== false) {
						return $return;
					}
				}
				$tried[$cname] = true;
			}
		}

		foreach ($this->regx as $cname => $regx) {
			if (isset($tried[$cname])) {
				continue;
			}
			if (preg_match($regx, $url, $m)) {
				if (($return = $this->parse_route($method, $this->routes[$cname], $m, $url)) !== false) {
					return $return;
				}
			}
		}

		return false;
	}

	protected function parse_route(string $method, array $route, $m, $url)
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

		if (!static::test_route_method($method, $route)) {
			return false;
		}

		$vars = static::get_route_vars($route[static::VARS], $m);
		$controller = $route[static::CONTROLLER];

		if (isset($route[static::ACTION]) and strlen($route[static::ACTION])) {//use hardcoded single value
			$action = $route[static::ACTION];
		} elseif (isset($vars['action']) and strlen($vars['action'])) {//or fallback to URL
			$action = $vars['action'];//@todo this is external var!! //@todo: make 'action' configurable or more unique
		} else {
			$action = $this->action_default;
		}

		if (isset($route[static::ACTION_MAPS]) and isset($route[static::ACTION_MAPS][$action])) {//e.g. update => edit or edit_v2_temp etc., use action_name => null to block
			$action = $route[static::ACTION_MAPS][$action];
		}

		return [$controller, $action, $vars];
	}

	public function get_rewrite($name, $vars = [])
	{
		$cname = crc32($name);
		if (isset($this->routes[$cname])) {
// 			return $this->make_link($this->routes[$cname], $vars/*, $params*/);
			$route =& $this->routes[$cname];
			$query_vars = array_diff_key($vars, $route[static::VARS]);
			$vars = array_replace($route[static::VARS], array_intersect_key($vars, $route[static::VARS]));
			$link = vsprintf($route[static::PRNTF], $vars);

			if ($query_vars) {
				$link .= '?' . http_build_query($query_vars, '', '&', PHP_QUERY_RFC3986);
			}

			return '/' . $link;
		} else {
			throw new Exception('NO_ROUTE; No link named ' . $name);
		}
	}

// 	protected function make_link($rewrite, $vars/*, $params*/)
// 	{
// 		$query_vars = array_diff_key($vars, $rewrite[static::VARS]);
// 		$vars = array_replace($rewrite[static::VARS], array_intersect_key($vars, $rewrite[static::VARS]));
// 		$link = vsprintf($rewrite[static::PRNTF], $vars);
// 
// 		if ($query_vars) {
// 			$link .= '?' . http_build_query($query_vars, null, '&', PHP_QUERY_RFC3986);
// 		}
// 
// 		return '/' . $link;
// 	}

	public function get_action_default()
	{
		return $this->action_default;
	}

	public static function test_route_method(string $method, array $route): bool
	{
		if ($route[static::METHOD] > 0) {
			if (isset(static::METHODS[$method])) {
				if (!(static::METHODS[$method] & $route[static::METHOD])) {
					return false;
				}
			}
		}

		return true;
	}

	public static function get_route_vars(array $route_vars, array $m)
	{
		array_shift($m);

		return array_combine(array_keys($route_vars), $m);
	}
}
