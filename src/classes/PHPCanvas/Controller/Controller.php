<?php

namespace PHPCanvas\Controller;

use PHPCanvas\Config;
use PHPCanvas\Routing\Dispatch;
use PHPCanvas\Http\Request;
use PHPCanvas\Http\Response;
use PHPCanvas\Scope\Scope;

class Controller
{
	protected Config $Config;
	protected Dispatch $Dispatch;
	protected Request $Request;
	protected Response $Response;
	protected Scope $Scope;

	public function __construct(Config $Config, Dispatch $Dispatch, Request $Request, Response $Response, Scope $Scope)
	{
		$this->Config = $Config;
		$this->Dispatch = $Dispatch;
		$this->Request = $Request;
		$this->Response = $Response;
		$this->Scope = $Scope;
	}
}
