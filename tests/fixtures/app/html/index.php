<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

//require once
// if fntion eixsts (myapp()->run);
// else http_response_code(503)
// exit('currently unavailble');
// ob_start();

(App\app())->run();
