<?php

namespace PHPCanvas\Routing;

use Exception;
use PHPUnit\Framework\TestCase;

class LinksTest extends TestCase
{
	protected Links $Links;
	protected Router $Router;

	public function setUp(): void
	{
		$this->Router = new Router();
		$this->Links = new Links($this->Router, 'example.com', '');
	}

	public function testCreate(): void
	{
		$this->assertInstanceOf(LinksInterface::class, $this->Links);
	}

	public function testGetLinkFail(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage("NOT_FOUND; No link found for \"not_found\"");

		$this->Links->get_link('not_found');
	}

	public function testGetLink(): void
	{
		$this->Router->add_route(
			name: 'test',
			path: '/simple',
			controller: 'App\\Controller\\Simple'
		);

		$res = $this->Links->get_link('test');
		$this->assertEquals('/simple', $res);

		$res = $this->Links->get_link('test', [], false);
		$this->assertEquals('//example.com/simple', $res);

		$res = $this->Links->get_link('test', [], false, 'https');
		$this->assertEquals('https://example.com/simple', $res);

		$Links = new Links($this->Router, 'example.com', 'path');

		$res = $Links->get_link('test');
		$this->assertEquals('/path/simple', $res);

		$res = $Links->get_link('test', [], false);
		$this->assertEquals('//example.com/path/simple', $res);

		$res = $Links->get_link('test', [], false, 'https');
		$this->assertEquals('https://example.com/path/simple', $res);
	}
}
