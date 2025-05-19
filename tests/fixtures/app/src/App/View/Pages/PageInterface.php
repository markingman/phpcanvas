<?php

namespace PHPCanvas\Test\App\View\Pages;

interface PageInterface
{
	public function __invoke(): string;
}
