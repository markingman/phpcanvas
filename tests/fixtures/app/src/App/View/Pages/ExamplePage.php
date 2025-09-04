<?php

namespace PHPCanvas\Test\App\View\Pages;

use PHPCanvas\Test\App\View\Layouts\Block;
use PHPCanvas\Test\App\View\Templates\DefaultTemplate;

class ExamplePage extends AbstractPage
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
		$title = $this->var === 'test' ? 'Test' : 'Example';

		return $this->renderTemplate(
			template: DefaultTemplate::class,
			title: $title,
			content: (new Block(
				title: $title,
			))(),
		);

	}
}
