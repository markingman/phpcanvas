<?php

if (false === function_exists('prx')) {
	function prx(...$vs): never
	{
		while (ob_get_length()) {
			ob_end_clean();
		}
	
		if (!str_contains(PHP_SAPI, 'cli')) {
			header('Content-Type: text/plain; charset=UTF-8');
		}
		
		foreach ($vs as $v) {
			var_export($v);
			echo PHP_EOL . PHP_EOL;
		}
	
		echo PHP_EOL;
	
		$b = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	
		echo $b[0]['file'] . ':' . $b[0]['line'];
	
		exit;
	}
}