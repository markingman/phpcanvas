<?php

namespace PHPCanvas\Test\App\Controller;

class ExampleController
{
	public function action_default(): void
	{
		echo "DEFAULT ";
		exit(__FILE__);
	}

	public function action_test(): void
	{
		echo "HERE ";
		exit(__FILE__);
	}
}
