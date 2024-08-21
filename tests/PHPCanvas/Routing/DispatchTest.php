<?php

namespace PHPCanvas\Routing;

use PHPCanvas\ContainerInterface;
use PHPCanvas\Http\RequestInterface;
use PHPCanvas\Routing\DispatchInterface;
use PHPCanvas\Routing\LinksInterface;
use PHPCanvas\Routing\RouterInterface;
use PHPUnit\Framework\TestCase;

class DispatchTest extends TestCase
{
	protected ContainerInterface $Container;
	protected RequestInterface $Request;
	protected RouterInterface $Router;
	protected LinksInterface $Links;
	protected DispatchInterface $Dispatch;

	public function setUp(): void
	{
// 		ContainerInterface $Container,
// 		RequestInterface $Request,
// 		RouterInterface $Router,
// 		LinksInterface $Links,

		$this->Dispatch = new Dispatch();
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