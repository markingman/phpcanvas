<?php

namespace PHPCanvas\Test\App\View\Pages;

class ExamplePage extends AbstractPage
{
	public function __construct(
		protected string $title,
		protected string $var1,
	) {
	}

	public function __invoke(): string
	{
		return (new Block(
			title: $title,
			var1: $var1
		))();
	}
}
