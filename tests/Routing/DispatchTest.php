<?php

namespace PHPCanvas\Routing;

use PHPCanvas\Container;
use PHPCanvas\ContainerInterface;
use PHPCanvas\Http\Request;
use PHPCanvas\Http\RequestInterface;
use PHPCanvas\Http\Response;
use PHPCanvas\Http\ResponseInterface;
// use PHPCanvas\Routing\Dispatch;
// use PHPCanvas\Routing\DispatchInterface;
// use PHPCanvas\Routing\Links;
// use PHPCanvas\Routing\LinksInterface;
// use PHPCanvas\Routing\Router;
// use PHPCanvas\Routing\RouterInterface;
use PHPUnit\Framework\TestCase;

class DispatchTest extends TestCase
{
	protected ContainerInterface $Container;
	protected RequestInterface $Request;
	protected ResponseInterface $Response;
	protected RouterInterface $Router;
	protected LinksInterface $Links;
	protected DispatchInterface $Dispatch;

	public function setUp(): void
	{
		$this->Container = new Container();
		$this->Request = new Request();
		$this->Response = new Response();
		$this->Router = new Router();
		$this->Links = new Links($this->Router, 'example.com', '');

		$this->Dispatch = new Dispatch(
			Container: $this->Container,
			Request: $this->Request,
			Response: $this->Response,
			Router: $this->Router,
			Links: $this->Links,
			action_prefix: '',
			action_suffix: '_handler',
		);
	}

	public function testCreate(): void
	{
		$this->assertInstanceOf(DispatchInterface::class, $this->Dispatch);
	}

	//call controller 1

	//call controller 2

	//call controller 3
	
	// get controller
	
	// get action

	// get link

	// go_to
	
	// get routes

	// instanciate

	// call
}
