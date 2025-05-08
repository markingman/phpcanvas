<?php

namespace PHPCanvas\Test\App\Controller;

use PHPCanvas\Test\App\Model\ExampleModel;

class IndexController
{
	protected ExampleModel $ExampleModel;

	public function __invoke(ExampleModel $ExampleModel)
	{
		$this->ExampleModel	= $ExampleModel;
	}

	public function action_default(): void
	{
		if ($this->ExampleModel->test()) {
			echo 'TRUE!!!';
		}
		echo "DEFAULT ";
		exit(__FILE__);
		
		$IndexPage->view('default');
	}

	public function action_test(): void
	{
		echo "HERE ";
		exit(__FILE__);
	}
}
