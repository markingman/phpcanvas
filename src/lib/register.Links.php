<?php

return function (PHPCanvas\ContainerInterface $c) {
	return new PHPCanvas\Routing\Links($c['Router'], $c['Config']->SITE_URL, $c['Config']->SITE_PATH);
};
