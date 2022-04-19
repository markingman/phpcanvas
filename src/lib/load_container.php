<?php

$Container = new PHPCanvas\Container();

if (isset($container_store)) {
	$container_store = is_string($container_store) ? realpath($container_store) : false;
} else {
	$container_store = realpath(__DIR__ . '/../../../../../var/classes');
}

if ($container_store) {
	$Container->set_store($container_store);
}

return $Container;
