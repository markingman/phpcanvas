<?php

namespace PHPCanvas\Test\App\Controller;

use PHPCanvas\Controller\Controller;

abstract class AbstractController extends Controller
{
	public function __construct(
		ControllerContext $ControllerContext
	) {
		parent::__construct(
			$ControllerContext->Config,
			$ControllerContext->Dispatch,
			$ControllerContext->Request,
			$ControllerContext->Response,
		);
	}

//	public function respond_page(PageInterface $Page): void
//	{
//		$this->Dispatch->call($this, 'load_partials');
//		$this->Response->html($Page());
//	}

//	public function load_partials(LoadPartials $LoadPartials): void
//	{
//		$LoadPartials();
//	}
}
