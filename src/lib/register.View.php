<?php

return function (PHPCanvas\ContainerInterface $c) {
	$View = new PHPCanvas\View\View(['url' => $c['Config']->SITE_URL, 'path' => $c['Config']->SITE_PATH]);
	$View->set_finder($c['ViewFinder']);
	$View->set_links($c['Links']);

	return $View;
};
