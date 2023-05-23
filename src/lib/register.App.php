<?php

return function (PHPCanvas\ContainerInterface $c) {
	return new PHPCanvas\Application($c['Config'], $c, $c['Request'], $c['Response'], $c['Dispatch']);
};
