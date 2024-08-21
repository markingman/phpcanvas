<?php

return function (PHPCanvas\ContainerInterface $c) {
	$ViewFinder = new PHPCanvas\Finder($c['Config']->DIRS_VIEW);
	$c->cache_put('ViewFinder', $ViewFinder);

	return $ViewFinder;
};