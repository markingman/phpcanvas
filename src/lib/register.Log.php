<?php

return function ($c) {
	$WriteErrorLog = new PHPCanvas\Logs\WriteErrorLog($c['Config']->DIR_LOGS, 'errors');

	return new PHPCanvas\Logs\LogHandler($WriteErrorLog);
};
