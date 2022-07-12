<?php

namespace PHPCanvas\Http;

class Request implements RequestInterface
{
	protected $method;
	protected $path;
	protected $headers;
	protected $ua;
	protected $ip;
	protected $ref;
	public $_GET = [];
	public $_POST = [];
	public $_FILES = [];
	public $_SERVER = [];
	public $_COOKIE = [];
	const GP = 0;
	const G = 1;
	const P = 2;

	public function __construct($headers = null, $get = null, $post = null, $files = null, $server = null, $cookie = null)
	{
		$this->set_headers($headers);
		$this->set_get($get);
		$this->set_post($post);
		$this->set_files($files);
		$this->set_server($server);
		$this->set_cookie($cookie);
	}

	public function set_headers($headers = null): void
	{
		$this->headers = $headers;//dont want to call apache_request_headers each page load, 'lazy load' default in get_header()
	}

	public function set_get($get = null): void
	{
		if (is_null($get)) {
			$this->set('_GET', $_GET);
		} else {
			$this->set('_GET', $get);
		}
	}

	public function set_post($post = null): void
	{
		if (is_null($post)) {
			$this->set('_POST', $_POST);
		} else {
			$this->set('_POST', $post);
		}
	}

	public function set_files($files = null): void
	{
		if (is_null($files)) {
			$this->set('_FILES', $_FILES);
		} else {
			$this->set('_FILES', $files);
		}
	}

	public function set_server($server = null): void
	{
		if (is_null($server)) {
			$this->set('_SERVER', $_SERVER);
		} else {
			$this->set('_SERVER', $server);
		}
	}

	public function set_cookie($cookie = null): void
	{
		if (is_null($cookie)) {
			$this->set('_COOKIE', $_COOKIE);
		} else {
			$this->set('_COOKIE', $cookie);
		}
	}

	public function set_method(string $method): void
	{
		$this->method = strtoupper((string)filter_var($method, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH));
	}

	public function set_path(string $url): void
	{
		$this->path = parse_url((string)filter_var($url, FILTER_SANITIZE_URL), PHP_URL_PATH);
	}

	public function set_ua($ua = null): void
	{
		if (is_string($ua)) {
			$this->ua = $ua;
		} elseif (is_null($this->ua)) {
			$this->ua = trim((string)filter_var(@$this->_SERVER['HTTP_USER_AGENT'], FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH));
		}
	}

	public function set_ip($ip = null):void //@todo: include forwarded for?
	{
		if (is_string($ip)) {
			$this->ip = $ip;
		} elseif (is_null($this->ip)) {
			//if forwarded for
			$this->ip = (string)filter_var(@$this->_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
		}
	}

	public function set_ref($ref): void
	{
		if (is_string($ref)) {
			$this->ref = $ref;
		} elseif (is_null($this->ref)) {
			//if proxy
			$this->ref = (string)filter_var(@$this->_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
		}
	}

	protected function set($var, &$values): void
	{
		$this->$var =& $values;
	}

// 	public function ua()
// 	{
// 		static $ua;
// 
// 		if (is_null($ua)) {
// 			$ua = trim((string)filter_var(@$this->_SERVER['HTTP_USER_AGENT'], FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH));
// 		}
// 
// 		return $ua;
// 	}

	public function get_header($header): string|null
	{
		if (is_null($this->headers)) {
			$this->headers = apache_request_headers();
		}

		if (isset($this->headers[$header])) {
			return $this->headers[$header];
		} else {
			return null;
		}
	}

	public function get_id(string|array $ids = 'id', int $where = self::GP, int $default = 0, int $min_range = 0)
	{
		foreach ((array)$ids as $id) {
			if ($where != self::P and isset($this->_GET[$id])) {
				return filter_var($this->_GET[$id], FILTER_SANITIZE_NUMBER_INT, ['default' => $default, 'min_range' => $min_range]);
			} elseif ($where != self::G and isset($this->_POST[$id])) {
				return filter_var($this->_POST[$id], FILTER_SANITIZE_NUMBER_INT, ['default' => $default, 'min_range' => $min_range]);
			}
		}

		return $default;
	}

	public function get_var($vars = 'arg1', int $where = self::GP, $default = '')
	{
		foreach ((array)$vars as $var) {
			if ($where != self::P and isset($this->_GET[$var])) {
				return filter_var($this->_GET[$var], FILTER_CALLBACK, ['options' => [$this, 'filter_regx_var']]);
			} elseif ($where != self::G and isset($this->_POST[$var]) and preg_match(REGX_VAR, (string)$this->_POST[$var])) {
				return filter_var($this->_POST[$var], FILTER_CALLBACK, ['options' => [$this, 'filter_regx_var']]);
			}
		}

		return $default;
	}

	public function get_val($vars = 'arg1', int $where = self::GP, $default = '')
	{
		foreach ((array)$vars as $var) {
			if ($where != self::P and isset($this->_GET[$var])) {
				return filter_var($this->_GET[$var], FILTER_CALLBACK, ['options' => [$this, 'filter_val']]);
			} elseif ($where != self::G and isset($this->_POST[$var])) {
				return filter_var($this->_POST[$var], FILTER_CALLBACK, ['options' => [$this, 'filter_val']]);
			}
		}

		return $default;
	}

	public function get_array($var, int $where = self::GP, $default = [], $filter = 'filter_val')
	{
		//force single level array
		$return = $default;
		if ($where != self::P and isset($this->_GET[$var])) {
			$return = array_values((array)$this->_GET[$var]);
		} elseif ($where != self::G and isset($this->_POST[$var])) {
			$return = array_values((array)$this->_POST[$var]);
		}

		foreach ($return as $i => $item) {
			if (!is_string($item)) {
				unset($return[$i]);
			}
		}

		return array_map([$this, $filter], array_values($return));
	}

	public function get_id_array($var, int $where = self::GP, array $default = [])
	{
		//force single level array
		$return = $default;
		if ($where != self::P and isset($this->_GET[$var])) {
			$return = array_values((array)$this->_GET[$var]);
		} elseif ($where != self::G and isset($this->_POST[$var])) {
			$return = array_values((array)$this->_POST[$var]);
		}

		foreach ($return as $i => $item) {
			if (!(int)filter_var($item, FILTER_SANITIZE_NUMBER_INT)) {
				unset($return[$i]);
			}
		}

		return array_values($return);
	}

	public function get_sel($vars = 'arg1', $opts, int $where = self::GP)
	{
		return in_array($sel = $this->get_var($vars, $where), $opts) ? $sel : $opts[0];
	}

	public function get_ua(): string
	{
		if (is_null($this->ua)) {
			$this->set_ua();
		}

		return $this->ua;
	}

	public function get_ip(): string
	{
		if (is_null($this->ip)) {
			$this->set_ip();
		}

		return $this->ip;
	}

	public function get_ref(): string
	{
		if (is_null($this->ref)) {
			$this->set_ref();
		}

		return $this->ref;
	}

	public function get_method(): string
	{
		if (is_null($this->method)) {
			$this->set_method((string)@$this->_SERVER['REQUEST_METHOD']);
		}

		return $this->method;
	}

	public function get_path(): string
	{
		if (is_null($this->path)) {
			$this->set_path((string)@$this->_SERVER['REDIRECT_URL']);
		}

		return $this->path;
	}

	public function get_request(?string $method = null, ?string $path = null): array
	{
		if (!is_null($method)) {
			$this->set_method($method);
		}

		if (!is_null($path)) {
			$this->set_path($path);
		}

		return [$this->get_method(), $this->get_path()];
	}

	public function is_ssl(?int $port = 443, bool $no_cache = false): bool
	{
		static $is_ssl;

		if (is_null($is_ssl) or $no_cache) {
			if (is_null($port)) {
				$port = 443;
			}
			$is_ssl = (!empty($this->_SERVER['HTTPS']) and !empty($this->_SERVER['SERVER_PORT']) and $this->_SERVER['SERVER_PORT'] === $port);
		}

		return $is_ssl;
	}

	public function is_post(): bool
	{
		return ($this->get_method() === 'POST');
	}

	public function is_ajax(bool $no_cache = false): bool
	{
		static $is_ajax;

		if (is_null($is_ajax) or $no_cache) {
			//or accept application/json header
			$is_ajax = (strtolower((string)filter_var(@$this->_SERVER['HTTP_X_REQUESTED_WITH'])) === 'xmlhttprequest');
		}

		return $is_ajax;
	}

	protected function filter_regx_var(mixed $value): string
	{
		return (is_string($value) and preg_match('~^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]**$~i', $value)) ? $value : '';
	}

	protected function filter_val(mixed $var): string
	{
		return trim((string)filter_var($var, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW));
	}
}
