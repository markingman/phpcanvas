<?php

namespace PHPCanvas\Test\App\View\Pages;

use LogicException;
use PHPCanvas\Test\App\View\Templates\AbstractTemplate;

abstract class AbstractPage implements PageInterface
{
	protected function renderTemplate(string $template, string $title, string $content): string
	{
		if (!class_exists($template) or !is_subclass_of($template, AbstractTemplate::class)) {
			throw new LogicException('Invalid template');
		}

		return (new $template($title, $content))();
	}
}
