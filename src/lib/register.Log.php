<?php

return function (PHPCanvas\ContainerInterface $c) {
	$WriteErrorLog = new PHPCanvas\Logs\WriteErrorLog($c['Config']->DIR_LOGS);

	return new PHPCanvas\Logs\LogHandler($WriteErrorLog);
};
