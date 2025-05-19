<?php

namespace PHPCanvas\Test\App\Controller;

use PHPCanvas\Controller\Controller;
use PHPCanvas\Test\App\View\LoadPartials;
use PHPCanvas\Test\App\View\Pages\PageInterface;

abstract class AbstractController extends Controller
{
	public function respond_page(PageInterface $Page): void
	{
		$this->Dispatch->call($this, 'load_partials');
		$this->Response->html($Page());
	}

	public function load_partials(LoadPartials $LoadPartials): void
	{
		$LoadPartials();
	}
}
