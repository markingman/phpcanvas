<?php declare(strict_types=1);

// Only for dev, not for production!
// Expected to run in container, see Dockerfile
// Runs pages set up in routes.json and pages/*.php

// use SportsBase\View\AppView as View;
// use SportsBase\View\Users\MembersLoginEntity;

$req = parse_url((isset($_SERVER['REQUEST_URI']) and is_string($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '', PHP_URL_PATH);
$root = realpath(__DIR__ . '/..');

if (!is_string($req)) {
	http_response_code(400);
	exit('Malformed request');
}

if (preg_match('/\.ico$/', $req)) {
// 	$src = $root . '/src/html/img/favicon.ico';
// 	header('Content-Type: image/x-icon');
// 	header('Content-Length: ' . filesize($src));
// 	header('Cache-Control: public, max-age=86400');
// 	readfile($src);
	http_response_code(404);
	exit;
}

// if (in_array($req, ['/dev/img/logo.svg', '/dev/img/logo-title.svg', '/dev/img/qr_code.png'])) {
// 	$src = $root . $req;
// 	header('Content-Type: image/svg+xml');
// 	header('Content-Length: ' . filesize($src));
// 	header('Cache-Control: public, max-age=86400');
// 	readfile($src);
// 	exit;
// }

if (preg_match('/\.(?:png|jpg|jpeg|gif|svg|css|js)$/', $req)) {
	return false;// serve resource as-is
}

require_once __DIR__ . '/../../bootstrap.php';

// session_start();

// client toggle

// if (!isset($_SESSION['client'])) {
// 	$_SESSION['client'] = false;
// }
// if ('switch' === ($_GET['view'] ?? '')) {
// 	$_SESSION['client'] = !$_SESSION['client'];
// }

// $_SESSION['user'] = match($_GET['user'] ?? 1) {
// 	'2' => 2,
// 	'3' => 3,
// 	'4' => 4,
// 	'5' => 5,
// 	'6' => 6,
// 	default => 1,
// };

// find route

// $routes = json_decode((string)file_get_contents(__DIR__ . '/routes.json'), true);

// if (!is_array($routes)) {
// 	http_response_code(500);
// 	exit('Could not decode routes');
// }
// 
// $file = false;
// 
// foreach ($routes as $route => $path) {
// 	if ($req === $route) {
// 		if (!empty($path) and is_string($path)) {
// 			$file = realpath(__DIR__ . '/pages/' . $path);
// 		}
// 		break;
// 	}
// }
// 
// if ($file === false) {
// 	http_response_code(404);
// 	exit('Route not found');
// }

// serve view

// try {
// 	(static function (): void {
	
		include __DIR__ . '/html/index.php';
	
// 	})();
// } catch (Exception $e) {
// 	http_response_code(500);
// 	exit($e->getMessage());
// }
