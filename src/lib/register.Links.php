<?php

return function (PHPCanvas\ContainerInterface $c): PHPCanvas\Routing\Links {
	if (!(($Config = $c->get('Config')) instanceof PHPCanvas\ConfigInterface)) {
		throw new Exception('Expected Container to have Config');
	}

	if (!(($Router = $c->get('Router')) instanceof PHPCanvas\Routing\RouterInterface)) {
		throw new Exception('Expected Container to have Router');
	}

	if (
		!isset($Config->SITE_URL) or !is_string($Config->SITE_URL)
		or !isset($Config->SITE_PATH) or !is_string($Config->SITE_PATH)
	) {
		throw new Exception('Expected Config to have SITE_URL and SITE_PATH');
	}

	return new PHPCanvas\Routing\Links($Router, $Config->SITE_URL, $Config->SITE_PATH);
};
