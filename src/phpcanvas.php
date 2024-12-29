<?php

if (false === function_exists('phpcanvas')) {

	function phpcanvas(string $path): PHPCanvas\Application
	{
		return require $path;
	}

}
