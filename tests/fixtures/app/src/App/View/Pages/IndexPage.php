<?php

namespace PHPCanvas\Test\App\View\Pages;

// use App\Entities\UserEntity;

class IndexPage extends AbstractPage
{	
	protected string $var;

	public function __construct(
		// protected UserEntity $UserEntity
	)
	{			
	}

	public function set_var(string $var): void
	{
		$this->var = $var;
	}

	public function __invoke(): string
	{
		// $username = $UserEntity->username;

		// if (empty($this->var)) {
			// throw new LogicException();
		// }

		return <<<__
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Test</title>
	</head>
	<body>hello, world</body>
</html>
__;
// 		$this->renderTemplate(
// 			title: 'Index',
// 			content: '<p>hello, world</p>',
// 		);
	}
}
