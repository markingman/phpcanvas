<?php

namespace PHPCanvas\Test\App\View\Pages;

readonly class IndexContext extends AbstractContext
{
	public function __construct(
		public bool $test
	) {
	}
}
