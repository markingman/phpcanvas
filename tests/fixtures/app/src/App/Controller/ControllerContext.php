<?php

namespace PHPCanvas\Test\App\Controller;

use PHPCanvas\Config;
use PHPCanvas\Http\Request;
use PHPCanvas\Http\Response;
use PHPCanvas\Routing\Dispatch;

readonly class ControllerContext
{
	public function __construct(
		public Config $Config,
		public Dispatch $Dispatch,
		public Request $Request,
		public Response $Response,
	) {
	}
}
