<?php

namespace PHPCanvas;

use \Exception;
use PHPCanvas\ConfigInterface;
use PHPCanvas\ContainerInterface;
use PHPCanvas\Http\RequestInterface;
use PHPCanvas\Http\ResponseInterface;
use PHPCanvas\Routing\DispatchInterface;

class Application
{
	const PHPCANVAS_VERSION = 2.0;
	public ConfigInterface $Config;
	public ContainerInterface $Container;
	public RequestInterface $Request;
	public ResponseInterface $Response;
	public DispatchInterface $Dispatch;

	public function __construct(
		ConfigInterface $Config,
		ContainerInterface $Container,
		RequestInterface $Request,
		ResponseInterface $Response,
		DispatchInterface $Dispatch
	) {
		$this->Config = $Config;
		$this->Container = $Container;
		$this->Request = $Request;
		$this->Response = $Response;
		$this->Dispatch = $Dispatch;
	}

	public function run(string $method = null, string $path = null)
	{
		try {
			$this->Dispatch->call_controller($method ?: $this->Request->get_method(), $path ?: $this->Request->get_path());
		} catch (Exception $e) {
			throw $e;
		}
	}
}
