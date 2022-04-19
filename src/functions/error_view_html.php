<?php

return function ($e, $m = null) {
	$html = <<<__
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>%1\$s</title>
	<style>*{line-height:1.6}</style>
</head>
<body>
	<pre><b>%1\$s</b>
%2\$s
%3\$s:%4\$s</pre>
</body>
</html>
__;
	echo vsprintf(
		$html,
		[
			1 => get_class($e),
			2 => $e->getMessage(),
			3 => $e->getFile(),
			4 => $e->getLine(),
		]
	);
};
