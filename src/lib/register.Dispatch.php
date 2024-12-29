<?php

return function (PHPCanvas\ContainerInterface $c): PHPCanvas\Routing\Dispatch {
	if (!(($Config = $c->get('Config')) instanceof PHPCanvas\ConfigInterface)) {
		throw new Exception('Expected Container to have Config');
	}

	if (!(($Links = $c->get('Links')) instanceof PHPCanvas\Routing\LinksInterface)) {
		throw new Exception('Expected Container to have Links');
	}

	if (!(($Request = $c->get('Request')) instanceof PHPCanvas\Http\RequestInterface)) {
		throw new Exception('Expected Container to have Request');
	}

	if (!(($Response = $c->get('Response')) instanceof PHPCanvas\Http\ResponseInterface)) {
		throw new Exception('Expected Container to have Response');
	}

	if (!(($Router = $c->get('Router')) instanceof PHPCanvas\Routing\RouterInterface)) {
		throw new Exception('Expected Container to have Router');
	}

	if (
		(isset($Config->ACTION_PREFIX) and !is_string($Config->ACTION_PREFIX))
		or (isset($Config->ACTION_SUFFIX) and !is_string($Config->ACTION_SUFFIX))
	) {
		throw new Exception('Expected Config to have SITE_URL and SITE_PATH');
	}

	return new PHPCanvas\Routing\Dispatch(
		$c, $Request, $Response, $Router, $Links,
		$Config->ACTION_PREFIX ?? '', $Config->ACTION_SUFFIX ?? ''
	);
};
