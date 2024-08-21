<?php

namespace PHPCanvas\Scope;

use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
	public function testSingleton()
	{
		$scope = new Scope;
		$scope->foo = 'bar';

		$scope2 = $scope;
		$scope2->bar = 'foo';

		$this->assertEquals($scope, $scope2);
		$this->assertTrue(isset($scope->bar));
		$this->assertTrue(isset($scope2->foo));
		$this->assertEquals('bar', $scope2->bar);
		$this->assertEquals('foo', $scope2->foo);
	}

	public function testScope()
	{
		$this->set_scope_data();
		$this->assertEquals(123, $this->get_scope_data());
	}

	private function set_scope_data()
	{
		Scope::instance()->test = 123;
	}

	private function get_scope_data()
	{
		return Scope::instance()->test;
	}
}