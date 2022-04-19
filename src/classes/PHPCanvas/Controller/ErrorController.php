<?php

namespace PHPCanvas\Controller;

use PHPCanvas\Errors\Errors;
use PHPCanvas\View\View;

class ErrorController extends Controller
{
	public function no_route($type, $vars)
	{
		Errors::http_err(404);
		new View('error');
		//$this->Response->
	}
}
