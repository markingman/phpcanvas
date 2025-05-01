<?php

return function (PHPCanvas\ContainerInterface $c): PHPCanvas\Routing\Links {
	if (!($Config = $c->get('Config')) instanceof PHPCanvas\ConfigInterface) {
		throw new LogicException('Expected Container to have Config');
	}

	if (!($Router = $c->get('Router')) instanceof PHPCanvas\Routing\RouterInterface) {
		throw new LogicException('Expected Container to have Router');
	}

	if (!isset($Config->SITE_URL) or !isset($Config->SITE_PATH)) {
		throw new LogicException('Expected Config to have SITE_URL and SITE_PATH');
	}

	return new PHPCanvas\Routing\Links($Router, $Config->SITE_URL, $Config->SITE_PATH);
};
