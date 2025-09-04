<?php

namespace PHPCanvas\Test\App\Controller;

use PHPCanvas\Test\App\View\Pages\ExamplePage;

class ExampleController extends AbstractController
{
	public function action_default(ExamplePage $ExamplePage): void
	{
		$ExamplePage->set_var('test');
		$this->respond_page($ExamplePage);
	}

	public function action_test(ExamplePage $ExamplePage): void
	{
		$ExamplePage->set_var('example');
		$this->respond_page($ExamplePage);
	}
}
