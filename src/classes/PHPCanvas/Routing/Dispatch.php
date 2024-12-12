<?php

namespace PHPCanvas\Routing;

use Exception;
use PHPCanvas\ContainerInterface;
use PHPCanvas\Http\RequestInterface;
use PHPCanvas\Http\ResponseInterface;

class Dispatch implements DispatchInterface
{
	protected ContainerInterface $Container;
	protected RequestInterface $Request;
	protected ResponseInterface $Response;
	protected RouterInterface $Router;
	protected LinksInterface $Links;
	protected string $action_prefix;
	protected string $action_suffix;
	protected ?string $controller = null;
	protected ?string $action = null;

	function __construct(
		ContainerInterface $Container,
		RequestInterface $Request,
		ResponseInterface $Response,
		RouterInterface $Router,
		LinksInterface $Links,
		?string $action_prefix = null,
		?string $action_suffix = null,
	) {
		$this->Container = $Container;
		$this->Request = $Request;
		$this->Response = $Response;
		$this->Router = $Router;
		$this->Links = $Links;
		$this->action_prefix = $action_prefix ?? '';
		$this->action_suffix = $action_suffix ?? '';
	}

	public function call_controller(?string $method = null, ?string $url = null): void
	{
		if (!$route = $this->Router->get_route((string)$method, (string)$url)) {
			// TODO response types, e.g. method not implemented
			throw new Exception(sprintf('DISPATCH_NO_ROUTE; No route found for URL "%s"', $url), 404);
		} else {
			[$controller, $action, $vars/*, $name*/] = $route;//TODO: route is class
		}

		if (str_starts_with($controller, 'http') and str_contains($controller, '://')) { //Router can make http redirect
			$this->go_to($controller);
		}

		$this->controller = $controller;
		$this->action = $this->action_prefix . $action . $this->action_suffix;
		foreach ($vars as $k => $v) {
			$this->Request->set_get_value($k, $v);
		}

// 		$name = !empty($route['name']) ? $route['name'] : null;//@todo: setable controller name in route
		// this->Reponse = $code

		if (!class_exists($this->controller)) {
			throw new Exception(sprintf('DISPATCH_NO_CONTROLLER; Could not load controller "%s"', $this->controller), 500);
		}

		try {
			$Controller = $this->instanciate($this->controller, /*$name,*/ /*true*/);
		} catch (Exception $e) {
			throw new Exception(sprintf('DISPATCH_FAILED_INSTANCIATE; Could not instanciate controller "%s"', $this->controller), 500, $e);
		}

		if (!is_object($Controller)) {
			throw new Exception(sprintf('DISPATCH_FAILED_INSTANCIATE; Unexpectant controller format "%s"', $this->controller), 500);
		}

		if (!is_callable([$Controller, $this->action])) {
			throw new Exception(sprintf('DISPATCH_NO_ACTION; Could not find action "%s::%s"', $this->controller, $this->action), 404);
		}

		try {
			if (is_callable([$Controller, '__invoke'])) {
				$this->call($Controller, '__invoke');
			}
		} catch (Exception $e) {
			throw new Exception(sprintf('DISPATCH_FAILED_INVOKE; Could not invoke controller "%s"', $this->controller), 500, $e);
		}

		try {
			$this->call($Controller, $this->action);
		} catch (Exception $e) {
			throw $e;
// 			throw new Exception(sprintf('DISPATCH_FAILED_ACTION; Could not call action "%s::%s"', $this->controller, $this->action), 500, $e);
		}
	}

	public function get_controller(): string
	{
		return $this->controller ?? '';
	}

	public function get_action(): string
	{
		return $this->action ?? '';
	}

	/** @param array<string, string> $vars */
	public function get_link(string $name, array $vars = [], bool $relative = true): string
	{
		return $this->Links->get_link($name, $vars, $relative);
	}

	/** @param array<string, string> $vars */
	public function go_to(string $name, array $vars = [], int $code = 307, bool $relative = true): never
	{
		$to = $this->get_link($name, $vars, $relative);
		$this->Response->redirect($to, $code);
		exit;
	}

	public function get_routes(): array
	{
		return $this->Router->get_routes();
	}

// 	public function get_rewrites()
// 	{
// 		return $this->Router->get_rewrites();
// 	}

	public function instanciate(string $class, /*string $name,*/ bool $store = false): mixed
	{
		return $this->Container->create($class, $store);
	}

	/** @param mixed[] $params */
	public function call(object $class, string $method, array $params = []): mixed
	{
		return $this->Container->call($class, $method, $params);
	}

	public function store(string $name, object $object)
	{
		return $this->Container->offsetSet($name, $object);
	}
}
