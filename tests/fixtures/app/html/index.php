<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

if (!function_exists('App\app')) {
	http_response_code(500);
	exit('Currently unavailable');
}

(App\app())->run();
