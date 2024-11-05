<?php

namespace PHPCanvas\Http;

interface ResponseInterface
{
	/*function set_request(\PHPCanvas\Http\RequestInterface $Request);

	function set_view(\PHPCanvas\View\ViewInterface $View);

	function set_scope(\PHPCanvas\Scope\ScopeInterface $Scope);

	function set_header($key, $value);

	function unset_header($key);

	function view($template = null, $vars = array());

	function html($template = null, $vars = array());

	function text($string = '')

	function json($vars = '')

	function respond($content = '');*/
	public function redirect(string $to, int $code = 303): void;
}
