<?php

namespace PHPCanvas\Routing;

use Exception;
use PHPCanvas\ContainerInterface;
use PHPCanvas\Http\RequestInterface;
use PHPCanvas\Routing\RouterInterface;
use PHPCanvas\Routing\LinksInterface;

class Dispatch implements DispatchInterface
{
	protected $Container;
	protected $Request;
	protected $Router;
	protected $Links;
	protected $action_prefix;
	protected $action_suffix;
	protected $controller = null;
	protected $action = null;

	function __construct(
		ContainerInterface $Container,
		RequestInterface $Request,
		RouterInterface $Router,
		LinksInterface $Links,
		$action_prefix,
		$action_suffix
	) {
		$this->Container = $Container;
		$this->Request = $Request;
		$this->Router = $Router;
		$this->Links = $Links;
		$this->action_prefix = $action_prefix;
		$this->action_suffix = $action_suffix;
	}

	public function call_controller(?string $method = null, ?string $url = null)
	{
		if (!$route = $this->Router->get_route((string)$method, (string)$url)) {
			throw new Exception(sprintf('NO_ROUTE; No route found for URL %s', $url), 404);
		} else {
			[$controller, $action, $vars/*, $name*/] = $route;
		}

		if (strpos($controller, 'http') === 0 and strpos($controller, '://') !== false) { //Router can make http redirect
			$this->Links->go_to($controller);
		}

		$this->controller = $controller;
		$this->action = $this->action_prefix . $action . $this->action_suffix;
		foreach ($vars as $k => $v) {
			$this->Request->_GET[$k] = $v;
		}
		$name = !empty($name) ? $name : null;//@todo: setable controller name in route
		// this->Reponse = $code

		if (!class_exists($this->controller)) {
			throw new Exception(sprintf('NO_CONTROLLER; Could not load controller %s', $this->controller), 500);
		}

		try {
			$Controller = $this->instanciate($this->controller, $name, true);
		} catch (Exception $e) {
			throw new Exception('FAILED_CTRL_INSTANCIATE; Could not instanciate controller: ' . $e->getMessage(), 500);
		}

		if (!is_callable([$Controller, $this->action])) {
			throw new Exception(sprintf('NO_ACTION; Could not call action for %s::%s', $this->controller, $this->action), 404);
		}

		try {
			if (is_callable([$Controller, '__invoke'])) {
				$this->call($Controller, '__invoke');
			}
		} catch (Exception $e) {
			throw new Exception('FAILED_CTRL_INVOKE; Could not call invoke ' . $e->getMessage(), 500);
		}

		try {
			$this->call($Controller, $this->action);
		} catch (Exception $e) {
			throw new Exception('FAILED_CTRL_ACTION; Could not call controller action; ' . $e->getMessage(), 500);
		}
	}

	public function get_controller()
	{
		return $this->controller;
	}

	public function get_action()
	{
		return $this->action;
	}

	public function get_link($name, array $vars = [], $relative = true)
	{
		return $this->Links->get_link($name, $vars, $relative);
	}

	public function go_to($name, array $vars = [], $code = 307)
	{
		return $this->Links->go_to($name, $vars, $code);
	}

	public function get_routes()
	{
		return $this->Router->get_routes();
	}

// 	public function get_rewrites()
// 	{
// 		return $this->Router->get_rewrites();
// 	}

	public function instanciate($class, $name, $store = false)
	{
		return $this->Container->instanciate($class, $name, $store);
	}

	public function call($class, $method, $params = [])
	{
		return $this->Container->call($class, $method, $params);
	}
}
