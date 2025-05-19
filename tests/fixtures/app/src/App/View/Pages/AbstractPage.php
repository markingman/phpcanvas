<?php

namespace PHPCanvas\Test\App\View\Pages;

use LogicException;

abstract class AbstractPage implements PageInterface
{
	protected function renderTemplate(string $template, string $title, string $content): string
	{
		if ($template !== 'default') {
			throw new LogicException('Unknown template $template');
		}

		$Template = new $Template($content);
		
		return $Template();
	}
}
