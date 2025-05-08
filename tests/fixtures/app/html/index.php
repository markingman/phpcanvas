<?php

//require once
// if fntion eixsts (myapp()->run);
// else http_response_code(503)
// exit('currently unavailble');
// ob_start();
require __DIR__ . '/../app.php';
(App\app())->run();
?>
<!DOCTYPE html>
<head>
	<title>Test</title>
	</head><html><body>hello, world</body>
</html>
