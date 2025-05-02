<?php

return function (): PHPCanvas\Http\Request {
	return new PHPCanvas\Http\Request(
		$_GET,
		$_POST,
		$_FILES,
		$_SERVER,
		$_COOKIE
	);
};
