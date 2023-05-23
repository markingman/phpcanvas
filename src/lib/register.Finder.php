<?php

return function (PHPCanvas\ContainerInterface $c) {
	$Finder = new PHPCanvas\Finder($c['Config']->DIRS_FINDER);
	$c->cache('Finder', $Finder);

	return $Finder;
};