<?php

namespace PHPCanvas\Routing;

use Exception;

class Links implements LinksInterface
{
	protected RouterInterface $Router;
	protected string $site_url;// e.g: example.com | example.com:80
	protected string $site_path = '';// e.g: /path

// 	protected bool $secure = false;

	function __construct(
		RouterInterface $Router,
		string $site_url,
		string $site_path = ''
	) {
		$this->Router = $Router;
		$this->site_url = trim($site_url, '/');
		if ($site_path = trim($site_path, '/')) {
			$this->site_path = '/' . $site_path;
		}
	}

	/** @param array<string, string> $vars
	 * @throws Exception
	 */
	public function get_link(string $name, array $vars = [], bool $relative = true, ?string $pcol = null): string
	{
		try {
			$link = $this->Router->get_rewrite($name, $vars);
		} catch (Exception $e) {
			throw new Exception(message: "NOT_FOUND; No link found for \"$name\"", previous: $e);
		}

		if ($relative) {
			return $this->site_path . $link;
		} else {
			return ($pcol ? $pcol . '://' : '//') . $this->site_url . $this->site_path . $link;
		}
	}

	//@todo: this would be called from repsonse->redirect($to, true, $code)//manually make the full headers with 301/302 etc.
// 	public function go_to(string $name, array $vars = [], $secure = false, $code = 301): array
// 	{
// 		if (strpos($name, '://') !== false) {
// 			$to = $name;
// 		} else {
// 			$to = $this->get_link($name, $vars, $secure);
// 		}
// 
// 		return [$code, $to];
// // 		while (ob_get_length() !== false) {
// // 			ob_end_clean();
// // 		}
// // 
// // 		header('Location: ' . $to, true, $code);
// // 		exit;
// 	}
}
