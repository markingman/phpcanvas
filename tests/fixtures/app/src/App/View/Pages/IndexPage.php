<?php

namespace PHPCanvas\Test\App\View\Pages;

class IndexPage extends AbstractPage
{
	public function __invoke(): string
	{
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
