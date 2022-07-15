<?php // $Id: ScopeTest.php 772 2018-05-17 09:12:20Z dev $

use PHPCanvas\Scope\Scope;

class ScopeTest extends PHPUnit_Framework_TestCase
{
	public function testSingleton()
	{
		$scope = Scope::instance();
		$scope->foo = 'bar';

		$scope2 = Scope::instance();
		$scope->bar = 'foo';

		$this->assertEquals($scope, $scope2);
		$this->assertTrue(isset($scope->bar));
		$this->assertTrue(isset($scope2->foo));
		$this->assertEquals($scope->bar, $scope2->bar);
		$this->assertEquals($scope->foo, $scope2->foo);
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