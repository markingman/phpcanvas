<?php

return function (PHPCanvas\ContainerInterface $c): PHPCanvas\Application {
	if (!($Config = $c->get('Config')) instanceof PHPCanvas\ConfigInterface) {
		throw new Exception('Expected Container to have Config');
	}

	if (!($Request = $c->get('Request')) instanceof PHPCanvas\Http\RequestInterface) {
		throw new Exception('Expected Container to have Request');
	}

	if (!($Response = $c->get('Response')) instanceof PHPCanvas\Http\ResponseInterface) {
		throw new Exception('Expected Container to have Response');
	}

	if (!($Dispatch = $c->get('Dispatch')) instanceof PHPCanvas\Routing\DispatchInterface) {
		throw new Exception('Expected Container to have Dispatch');
	}

	return new PHPCanvas\Application($Config, $c, $Request, $Response, $Dispatch);
};
