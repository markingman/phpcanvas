<?php

namespace PHPCanvas\View;

interface HtmlInterface
{
	public function htmlentities(string $s): string;

	public function htmlspecialchars(string $s, array $t = null, bool $strip = true): string;

	public function htmlatts(array $atts = [], array $mask = []): string;

	public function tag(string $tag, array $atts = null, string $html = ''): string;
}
