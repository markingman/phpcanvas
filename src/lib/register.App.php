<?php

return function ($c) {
	return new PHPCanvas\Application($c['Config'], $c, $c['Request'], $c['Response'], $c['Dispatch']);
};
