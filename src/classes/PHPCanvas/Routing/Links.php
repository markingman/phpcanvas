<?php

namespace PHPCanvas\Routing;

use Exception;
use PHPCanvas\Routing\RouterInterface;

class Links implements LinksInterface
{
	protected $Router;
	protected $site_url;
	protected $site_path;
	protected $secure = false;

	function __construct(
		RouterInterface $Router,
		$site_url,
		$site_path = ''
	) {
		$this->Router = $Router;
		$this->site_url = trim($site_url, '/');
		$this->site_path = trim($site_path, '/');
	}

	function get_link($name, array $vars = [], $relative = true, $pcol = null)
	{
		if (!$link = $this->Router->get_rewrite($name, $vars)) {
			throw new Exception("No link found for {$controller_action}");
		}

		if ($relative) {
			return $this->site_path . $link;
		} else {
			return ($pcol ?: '//') . $this->site_url . $link;
		}
	}

	//@todo: this would be called from repsonse->redirect($to, true, $code)//manually make the full headers with 301/302 etc.
	function go_to($name, array $vars = [], $secure = false, $code = 301)
	{
		if (strpos($name, '://') !== false) {
			$to = $name;
		} else {
			$to = $this->get_link($name, $vars, $secure);
		}

		while (ob_get_length() !== false) {
			ob_end_clean();
		}

		header('Location: ' . $to, true, $code);
		exit;
	}
}
