<?php

namespace PHPCanvas\Http;

class Request implements RequestInterface
{
	const REGX_VAR = '~^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$~i';
	protected ?string $method = null;
	protected ?string $path = null;
	protected ?string $ua = null;
	protected ?string $ip = null;
	protected ?string $ref = null;
	/** @var array<string> $headers */
	protected ?array $headers = null;
	/** @var array<string|mixed> $_GET */
	public array $_GET = [];
	/** @var array<string|mixed> $_POST */
	public array $_POST = [];
	/** @var array<string|mixed> $_FILES */
	public array $_FILES = [];
	/** @var array<string|mixed> $_SERVER */
	public array $_SERVER = [];
	/** @var array<string|string> $_COOKIE */
	public array $_COOKIE = [];

	/**
	 * @param array<string, mixed>|null $get
	 * @param array<string, mixed>|null $post
	 * @param array<string, mixed>|null $files
	 * @param array<string, mixed>|null $server
	 * @param array<string, string>|null $cookie
	 */
	public function __construct(?array $get = null, ?array $post = null, ?array $files = null, ?array $server = null, ?array $cookie = null)
	{
		$this->set_get($get);
		$this->set_post($post);
		$this->set_files($files);
		$this->set_server($server);
		$this->set_cookie($cookie);
	}

	/**
	 * @param array<string, mixed>|null $get
	 */
	public function set_get(?array $get = null): void
	{
		$this->set('_GET', is_null($get) ? $_GET : $get);
	}

	/**
	 * @param array<string, mixed>|null $post
	 */
	public function set_post(?array $post = null): void
	{
		$this->set('_POST', is_null($post) ? $_POST : $post);
	}

	/**
	 * @param array<string, mixed>|null $files
	 */
	public function set_files(?array $files = null): void
	{
		$this->set('_FILES', is_null($files) ? $_FILES : $files);
	}

	/**
	 * @param array<string, mixed>|null $server
	 */
	public function set_server(?array $server = null): void
	{
		$this->set('_SERVER', is_null($server) ? $_SERVER : $server);
	}

	/**
	 * @param array<string, string>|null $cookie
	 */
	public function set_cookie(?array $cookie = null): void
	{
		$this->set('_COOKIE', is_null($cookie) ? $_COOKIE : $cookie);
	}

	/**
	 * @param array<string>|null $headers
	 */
	public function set_headers(?array $headers = null): void
	{
		if (is_null($headers)) {
			$this->headers = /*is_callable('getallheaders') ? */getallheaders()/* : []*/;
		} else {
			$this->headers = $headers;
		}
	}

	public function set_method(string $method = null): void
	{
		$this->method = strtoupper((string)filter_var($method, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH));
	}

	public function set_path(string $url): void
	{
		if (is_string($val = filter_var($url, FILTER_SANITIZE_URL))) {
			if (is_string($val = parse_url($val, PHP_URL_PATH))) {
				$this->path = $val;
			}
		}
	}

	public function set_ua(string $ua): void
	{
		$this->ua = trim((string)filter_var($ua, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH));
	}

	public function set_ip(string $ip): void
	{
		$this->ip = (string)filter_var($ip, FILTER_VALIDATE_IP);
	}

	public function set_ref(?string $ref = null): void
	{
		// referrer

		$this->ref = (string)filter_var($ref, FILTER_SANITIZE_URL);
	}

	public function set_get_value(string $k, string $v): void
	{
		$this->_GET[$k] = $v;
	}

	/**
	 * $param string $var
	 * @param array<string, mixed> $values
	 */
	protected function set(string $var, array $values): void
	{
		$this->$var = $values;
	}

	public function get_header(string $header): string|null
	{
		if (is_null($this->headers)) {
			$this->set_headers();
		}

		return $this->headers[$header] ?? null;
	}

	public function get_get(string $key): ?string
	{
		return (isset($this->_GET[$key]) and is_string($this->_GET[$key])) ? $this->_GET[$key] : null;
	}

	public function get_post(string $key): ?string
	{
		return (isset($this->_POST[$key]) and is_string($this->_POST[$key])) ? $this->_POST[$key] : null;
	}

	/**  @return array<string, string|int>|null */
	public function get_file(string $key): ?array
	{
		return (!empty($this->_FILES[$key]) and is_array($this->_FILES[$key]) )? $this->_FILES[$key] : null;
	}

	public function get_server(string $key): ?string
	{
		return (isset($this->_SERVER[$key]) and is_string($this->_SERVER[$key])) ? $this->_SERVER[$key] : null;
	}

	public function get_cookie(string $key): ?string
	{
		return $this->_COOKIE[$key] ?? null;
	}

	// The following get_* methods are all helper shorthand methods

	/**  @param string|string[] $vars */
	public function get_int_from_get(string|array $vars = 'id', int $default = 0, /* params*/ int $min_range = 0): int
	{
		return $this->get_int('_GET', $vars, $default, $min_range);
	}

	/**  @param string|string[] $vars */
	public function get_int_from_post(string|array $vars = 'id', int $default = 0, int $min_range = 0): int
	{
		return $this->get_int('_POST', $vars, $default, $min_range);
	}

	/**  @param string|string[] $vars */
	protected function get_int(string $where, string|array $vars = 'id', int $default = 0, int $min_range = 0): int
	{
		foreach ((array)$vars as $var) {
			if (isset($this->$where[$var]) and is_string($this->$where[$var])) {
				return $this->filter_int($this->$where[$var], $default, $min_range);
			}
		}

		return $default;
	}

	/**  @param string|string[] $vars */
	public function get_var_from_get(string|array $vars = 'arg1', string $default = ''): string
	{
		return $this->get_var('_GET', $vars, $default);
	}

	/**  @param string|string[] $vars */
	public function get_var_from_post(string|array $vars = 'arg1', string $default = ''): string
	{
		return $this->get_var('_POST', $vars, $default);
	}

	/**  @param string|string[] $vars */
	protected function get_var(string $where, string|array $vars = 'arg1', string $default = ''): string
	{
		foreach ((array)$vars as $var) {
			if (isset($this->$where[$var]) and is_string($this->$where[$var])) {
				return $this->filter_regx_var($this->$where[$var]);
			}
		}

		return $default;
	}

	/**  @param string|string[] $vars */
	public function get_val_from_get(string|array $vars = 'arg1', string $default = ''): string
	{
		return $this->get_val('_GET', $vars, $default);
	}

	/**  @param string|string[] $vars */
	public function get_val_from_post(string|array $vars = 'arg1', string $default = ''): string
	{
		return $this->get_val('_POST', $vars, $default);
	}

	/**  @param string|string[] $vars */
	protected function get_val(string $where, string|array $vars = 'arg1', string $default = ''): string
	{
		foreach ((array)$vars as $var) {
			if (isset($this->$where[$var]) and is_string($this->$where[$var])) {
				return $this->filter_val($this->$where[$var]);
			}
		}

		return $default;
	}

	/**
	 * @param string $var
	 * @param array<string> $opts
	 * @param string $default
	 * @return string
	 */
	public function get_sel_from_get(string $var = 'arg1', array $opts = [], string $default = ''): string
	{
		return $this->get_sel('_GET', $var, $opts, $default);
	}

	/**
	 * @param string $var
	 * @param array<string> $opts
	 * @param string $default
	 * @return string
	 */
	public function get_sel_from_post(string $var = 'arg1', array $opts = [], string $default = ''): string
	{
		return $this->get_sel('_POST', $var, $opts, $default);
	}

	/**
	 * @param string $where
	 * @param string $var
	 * @param array<string> $opts
	 * @param string $default
	 * @return string
	 */
	public function get_sel(string $where, string $var = 'arg1', array $opts = [], string $default = ''): string
	{
		return in_array($sel = $this->get_var($where, $var), $opts) ? $sel : $default;
	}

	/**
	 * @param string $var
	 * @param string[] $default
	 * @param string $filter
	 * @return string[]
	 */
	public function get_array_from_get(string $var, array $default = [], string $filter = 'filter_val'): array
	{
		return $this->get_array('_GET', $var, $default, $filter);
	}

	/**
	 * @param string $var
	 * @param string[] $default
	 * @param string $filter
	 * @return string[]
	 */
	public function get_array_from_post(string $var, array $default = [], string $filter = 'filter_val'): array
	{
		return $this->get_array('_POST', $var, $default, $filter);
	}

	/**
	 * @param string $where
	 * @param string $var
	 * @param string[] $default
	 * @param string $filter
	 * @return array<int, string>
	 */
	protected function get_array(string $where, string $var, array $default = [], string $filter = 'filter_val'): array
	{
		$return = $this->get_single_level_array($where, $var, $default);

		$return = array_filter($return, 'is_string');

		return array_map(callback: function($value) use ($filter): string {
			return $this->$filter($value);
		}, array: array_values($return));
	}




// 	public function get_int_array_from_get(string $var, array $default = []): array
// 	{
// 		return $this->get_int_array('_GET', $var, $default);
// 	}
// 
// 	public function get_int_array_from_post(string $var, array $default = []): array
// 	{
// 		return $this->get_int_array('_POST', $var, $default);
// 	}
// 
// 
// 	public function get_int_array(string $where, string $var, array $default = []): array
// 	{
// 		$return = $this->get_single_level_array($where, $var, $default);
// 
// 		foreach ($return as $i => $item) {
// /// same as get_int filter
// 			if (!(int)filter_var($item, FILTER_SANITIZE_NUMBER_INT)) {
// 				unset($return[$i]);
// 			}
// 		}
// 
// 		return array_values($return);
// 	}





	public function get_ua(): string
	{
		if (is_null($this->ua)) {
			$this->set_ua(trim((string)filter_var(@$this->_SERVER['HTTP_USER_AGENT'], FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)));
		}

		return $this->ua ?? '';
	}

	public function get_ip(): string
	{
		if (is_null($this->ip)) {
			if (isset($this->_SERVER['HTTP_X_FORWARDED_FOR']) and is_string($this->_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$this->set_ip($this->_SERVER['HTTP_X_FORWARDED_FOR']);
			} elseif (is_string($this->_SERVER['REMOTE_ADDR'])) {
				$this->set_ip($this->_SERVER['REMOTE_ADDR']);
			}
		}

		return $this->ip ?? '';
	}

	public function get_ref(): string
	{
		if (is_null($this->ref)) {
			//TODO: if proxy
			if (is_string($this->_SERVER['HTTP_REFERER'])) {
				$this->set_ref($this->_SERVER['HTTP_REFERER']);
			}
		}

		return $this->ref ?? '';
	}

	public function get_method(): string
	{
		if (is_null($this->method)) {
			if (is_string($method = $this->_SERVER['REQUEST_METHOD'] ?? '')) {
				$this->set_method($method);
			}
		}

		return $this->method ?? '';
	}

	public function get_path(): string
	{
		if (is_null($this->path)) {
			if (is_string($path = $this->_SERVER['REDIRECT_URL'] ?? '')) {
				$this->set_path($path);
			}
		}

		return $this->path ?? '';
	}

	/**  @return string[] */
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
			//TODO: or accept application/json header
			$is_ajax = (strtolower((string)filter_var($this->_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest');
		}

		return $is_ajax;
	}

	protected function filter_regx_var(mixed $value/*, array $opts*/): string
	{
		return (string)filter_var($value, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => static::REGX_VAR]]);
    
	}

	protected function filter_val(mixed $var/*, array $opts*/): string
	{
		return trim((string)filter_var($var, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW/*, opts*/));
	}

	protected function filter_int(mixed $var, /*, array $opts*/int $default = 0, int $min_range = 0): int
	{
		return (int)filter_var($var, FILTER_SANITIZE_NUMBER_INT, /*$opts*/['default' => $default, 'min_range' => $min_range]);
	}

	/**
	 * @param string[] $default
	 * @return string[]
	 * */
	protected function get_single_level_array(string $where, string|int $var, array $default): array
	{
		if (isset($this->$where[$var])) {
			return array_values((array)$this->$where[$var]);
		} else {
			return $default;
		}
	}
}
