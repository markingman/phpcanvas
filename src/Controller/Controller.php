<?php

namespace PHPCanvas\Controller;

use PHPCanvas\Config;
use PHPCanvas\Http\Request;
use PHPCanvas\Http\Response;
use PHPCanvas\Routing\Dispatch;

class Controller
{
	public function __construct(
		protected Config $Config,
		protected Dispatch $Dispatch,
		protected Request $Request,
		protected Response $Response
	) {
	}
}
