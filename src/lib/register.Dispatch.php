<?php

return function (PHPCanvas\ContainerInterface $c) {
	return new PHPCanvas\Routing\Dispatch(
		$c, $c['Request'], $c['Router'], $c['Links'],
		$c['Config']->ACTION_PREFIX, $c['Config']->ACTION_SUFFIX
	);
};
