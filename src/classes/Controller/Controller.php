<?php

namespace PHPCanvas\Controller;

use PHPCanvas\Config;
use PHPCanvas\Http\Request;
use PHPCanvas\Http\Response;
use PHPCanvas\Routing\Dispatch;

class Controller
{
	protected Config $Config;
	protected Dispatch $Dispatch;
	protected Request $Request;
	protected Response $Response;

	public function __construct(Config $Config, Dispatch $Dispatch, Request $Request, Response $Response)
	{
		$this->Config = $Config;
		$this->Dispatch = $Dispatch;
		$this->Request = $Request;
		$this->Response = $Response;
	}
}
