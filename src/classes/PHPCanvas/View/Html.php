<?php

namespace PHPCanvas\View;

class Html implements HtmlInterface
{
	public function htmlentities(string $s): string
	{
		return htmlentities($s, ENT_QUOTES | ENT_HTML5/*, null, false*/);
	}

	public function htmlspecialchars(string $s, array $t = null, bool $strip = true): string
	{
// 		if (strpos($s, '<') === false) {
// 			return htmlspecialchars($s, ENT_NOQUOTES | ENT_HTML5/*, null, false*/);
// 		}

		if ($t) {
			$o = chr(2);
			$c = chr(3);

			if ($t and $r = (strpos($s, $o) !== false)) {
				$s = str_replace([$o, $c], '', $s);
			}

			if ($strip) {
				$s = strip_tags($s, '<' . implode('><', $t) . '>');
			}

			$s = preg_replace(
				'~' . '<' . '(/*(' . implode('|', $t) . ')( [^>]*)*)' . '>' . '~',
				$o . '$1' . $c, $s
			);
		} elseif ($strip) {
			strip_tags($s);
		}

		$s = htmlspecialchars($s, ENT_NOQUOTES | ENT_HTML5,/* null, false*/);

		if ($t) {
			$s = str_replace([$o, $c], ['<', '>'], $s);
		}

		return $s;
	}

	public function htmlatts(array $atts = [], array $mask = []): string
	{
		if ($mask) {
			$atts = array_diff_key($atts, array_flip($mask));
		}

		$ret = '';
		foreach ($atts as $k => $v) {
			if (is_bool($v)) {
				if ($v) {
					$ret .= $k . ' ';
				}
			} elseif (!is_null($v)) {
				$ret .= $k . '="' . $this->htmlentities($v) . '" ';
			}
		}
		$ret = substr($ret, 0, -1);

		return $ret;
	}

	public function tag(string $tag, array $atts = null, string $html = ''): string
	{
		// e.g:
		// tag('img/', ['src' => 'a.jpg']) 
		// tag('p', null, 'Hello, <b>world</b>')
		// tag('/div')

		if ($close = strpos($tag, '/')) {
			$tag = rtrim($tag, '/');
		}
		$s = '<' . $tag;
		if ($atts) {
			$s .= ' ' . $this->htmlatts($atts);
		}
		if ($html) {
			$s .= '>' . $html;
		}
		if ($close) {
			$s .= $html ? '</' . $tag : ' /';
		}
		$s .= '>';

		return $s;
	}
}
