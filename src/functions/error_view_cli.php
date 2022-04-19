<?php

return function ($e, $m = null) {
	echo vsprintf(
		PHP_EOL .
		"\033[3;101m  %1\$s  \033[0m" . PHP_EOL .
		'%2$s' . PHP_EOL .
		'%3$s:%4$s' . PHP_EOL .
		PHP_EOL,
		[
			1 => get_class($e),
			2 => $e->getMessage(),
			3 => $e->getFile(),
			4 => $e->getLine(),
		]
	);
	exit($e->getCode());
};