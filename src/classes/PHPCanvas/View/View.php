<?php

namespace PHPCanvas\View;

use Exception;
use PHPCanvas\FinderInterface;
use PHPCanvas\Scope\ScopeInterface;
use PHPCanvas\Routing\LinksInterface;

class View implements ViewInterface
{
	protected ?FinderInterface $Finder = null;
	protected ?ScopeInterface $Scope = null;
	protected ?LinksInterface $Links = null;
	protected ?HtmlInterface $Html = null;

	public function set_finder(FinderInterface $Finder): void
	{
		$this->Finder = $Finder;
	}

	public function set_scope(ScopeInterface $Scope): void
	{
		$this->Scope = $Scope;
	}

	public function set_links(LinksInterface $Links): void
	{
		$this->Links = $Links;
	}

	public function set_html(HtmlInterface $Html): void
	{
		$this->Html = $Html;
	}

	public function get_view(string $view, string $search_path = null, bool $use_cache = true): string
	{
		if (str_starts_with($view, '/')) {
			return $view;
		} elseif ($path = $this->Finder?->get($view, $search_path, $use_cache)) {
				return $path;
		} else {
			throw new Exception('Could not find view "' . $view . '"');
		}

	}

	public function get_link(string $name, array $vars = [], bool $relative = true): string
	{
		return $this->Links?->get_link($name, $vars, $relative) ?: '';
	}

	public function view(string $view, array $_VARS = null, $search_path = null, $cache = true): mixed
	{
		$_TMPL = $this->get_view($view, $search_path, $cache);
		$_VARS = (array)$_VARS;

		ob_start();

		$return = call_user_func(
			function () use ($_TMPL, $_VARS) {
				if ($_VARS) {
					extract($_VARS, EXTR_REFS);
				}

				return require $_TMPL;
			}
		);

		$buffer = (string)ob_get_clean();

		return ($return === 1) ? $buffer : $return;
	}
}
