<?php

return function (PHPCanvas\ContainerInterface $c) {
	if (!(($Config = $c->get('Config')) instanceof PHPCanvas\Config)) {
		throw new Exception('Expected Container to have Config');
	}

	if (!isset($Config->DIR_LOGS) or !is_string($Config->DIR_LOGS)) {
		throw new Exception('Expected Config to have DIR_LOGS');
	}

	$WriteErrorLog = new PHPCanvas\Logs\WriteErrorLog($Config->DIR_LOGS);

	return new PHPCanvas\Logs\LogHandler($WriteErrorLog);
};
