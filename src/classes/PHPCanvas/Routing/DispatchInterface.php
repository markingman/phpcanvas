<?php

namespace PHPCanvas\Routing;

interface DispatchInterface
{
	public function call_controller(?string $method = null, ?string $url = null): void;
}
