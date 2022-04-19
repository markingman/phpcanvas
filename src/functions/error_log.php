<?php

return function ($e, $Log) {
	$sev = is_callable([$e, 'getSeverity']) ? $e->getSeverity() : error_get_last()['type'];
	$log = sprintf(
		"%s\t%s\t%s\t%s",
		$sev, $e->getCode(), $e->getMessage(), $e->getFile() . ':' . $e->getLine()
	);
	$lev = @[
		E_ERROR => LOG_ERR,
		E_WARNING => LOG_WARNING,
		E_PARSE => LOG_ALERT,
		E_NOTICE => LOG_NOTICE,
		E_CORE_ERROR => LOG_ERR,
		E_CORE_WARNING => LOG_WARNING,
		E_COMPILE_ERROR => LOG_ERR,
		E_COMPILE_WARNING => LOG_ALERT,
		E_USER_ERROR => LOG_ERR,
		E_USER_WARNING => LOG_WARNING,
		E_USER_NOTICE => LOG_NOTICE,
		E_STRICT => LOG_NOTICE,
		E_RECOVERABLE_ERROR => LOG_ERR,
		E_DEPRECATED => LOG_NOTICE,
		E_USER_DEPRECATED => LOG_NOTICE,
		E_ALL => LOG_NOTICE
	][$sev] ?: LOG_CRIT;
	$Log->log($log, $lev);
};
