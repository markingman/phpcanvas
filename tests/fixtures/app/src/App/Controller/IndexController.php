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
		exit(<<<__
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Test</title>
	</head>
	<body>hello, world</body>
</html>
__);
	}

	public function action_test(): void
	{
		echo "HERE ";
		exit(__FILE__);
	}
}
