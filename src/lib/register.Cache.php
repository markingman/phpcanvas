<?php

return function (PHPCanvas\ContainerInterface $c) {
	return new PHPCanvas\Cache\Cache($c['Config']->DIR_CACHE, $c['Config']->PRM_DIR_COPY);
};
