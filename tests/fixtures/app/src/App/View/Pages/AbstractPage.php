<?php

namespace PHPCanvas\Test\App\View\Pages;

use PHPCanvas\Test\App\View\HTMLContext;
use PHPCanvas\Test\App\View\Preloader;

abstract class AbstractPage implements PageInterface
{
	public function __construct(
		protected Preloader $Preloader,
		protected HTMLContext $HTMLContext
	) {
	}

	public function preload(string ...$paths): void
	{
		$this->Preloader->preload($paths);
	}
}
