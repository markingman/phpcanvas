<?php

return function (PHPCanvas\ContainerInterface $c): PHPCanvas\Logs\LogHandler {
	if (!($Config = $c->get('Config')) instanceof PHPCanvas\ConfigInterface) {
		throw new Exception('Expected Container to have Config');
	}

	if (!isset($Config->DIR_LOGS)) {
		throw new Exception('Expected Config to have DIR_LOGS');
	}

	$WriteErrorLog = new PHPCanvas\Logs\WriteErrorLog($Config->DIR_LOGS);

	return new PHPCanvas\Logs\LogHandler($WriteErrorLog);
};
