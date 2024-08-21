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
	protected ?array $headers = null;
	public array $_GET = [];
	public array $_POST = [];
	public array $_FILES = [];
	public array $_SERVER = [];
	public array $_COOKIE = [];

	public function __construct(?array $get = null, ?array $post = null, ?array $files = null, ?array $server = null, ?array $cookie = null)
	{
		$this->set_get($get);
		$this->set_post($post);
		$this->set_files($files);
		$this->set_server($server);
		$this->set_cookie($cookie);
	}

	public function set_get(?array $get = null): void
	{
		$this->set('_GET', is_null($get) ? $_GET : $get);
	}

	public function set_post(?array $post = null): void
	{
		$this->set('_POST', is_null($post) ? $_POST : $post);
	}

	public function set_files(?array $files = null): void
	{
		$this->set('_FILES', is_null($files) ? $_FILES : $files);
	}

	public function set_server(?array $server = null): void
	{
		$this->set('_SERVER', is_null($server) ? $_SERVER : $server);
	}

	public function set_cookie(?array $cookie = null): void
	{
		$this->set('_COOKIE', is_null($cookie) ? $_COOKIE : $cookie);
	}

	public function set_headers(?array $headers = null): void
	{
		if (is_null($headers)) {
			$this->headers = is_callable('getallheaders') ? getallheaders() : [];
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
		$this->path = parse_url((string)filter_var($url, FILTER_SANITIZE_URL), PHP_URL_PATH);
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
		return $this->_GET[$key] ?? null;
	}

	public function get_post(string $key): ?string
	{
		return $this->_POST[$key] ?? null;
	}

	public function get_file(string $key): ?array
	{
		return $this->_FILES[$key] ?? null;
	}

	public function get_server(string $key): ?string
	{
		return $this->_SERVER[$key] ?? null;
	}

	public function get_cookie(string $key): ?string
	{
		return $this->_COOKIE[$key] ?? null;
	}

	// The following get_* methods are all helper shorthand methods

	public function get_int_from_get(string|array $vars = 'id', int $default = 0, /* params*/ int $min_range = 0): int
	{
		return $this->get_int('_GET', $vars, $default, $min_range);
	}

	public function get_int_from_post(string|array $vars = 'id', int $default = 0, int $min_range = 0): int
	{
		return $this->get_int('_POST', $vars, $default, $min_range);
	}

	protected function get_int(string $where, string|array $vars = 'id', int $default = 0, int $min_range = 0): int
	{
		foreach ((array)$vars as $var) {
			if (isset($this->$where[$var]) and is_string($this->$where[$var])) {
				return $this->filter_int($this->$where[$var], $default, $min_range);
			}
		}

		return $default;
	}

	public function get_var_from_get(string|array $vars = 'arg1', string $default = ''): string
	{
		return $this->get_var('_GET', $vars, $default);
	}

	public function get_var_from_post(string|array $vars = 'arg1', string $default = ''): string
	{
		return $this->get_var('_POST', $vars, $default);
	}

	protected function get_var(string $where, string|array $vars = 'arg1', string $default = ''): string
	{
		foreach ((array)$vars as $var) {
			if (isset($this->$where[$var]) and is_string($this->$where[$var])) {
				return $this->filter_regx_var($this->$where[$var]);
			}
		}

		return $default;
	}

	public function get_val_from_get($vars = 'arg1', string $default = ''): string
	{
		return $this->get_val('_GET', $vars, $default);
	}

	public function get_val_from_post($vars = 'arg1', string $default = ''): string
	{
		return $this->get_val('_POST', $vars, $default);
	}

	protected function get_val(string $where, string|array $vars = 'arg1', string $default = ''): string
	{
		foreach ((array)$vars as $var) {
			if (isset($this->$where[$var]) and is_string($this->$where[$var])) {
				return $this->filter_val($this->$where[$var]);
			}
		}

		return $default;
	}

	public function get_sel_from_get(string $var = 'arg1', array $opts = [], string $default = ''): string
	{
		return $this->get_sel('_GET', $var, $opts, $default);
	}

	public function get_sel_from_post(string $var = 'arg1', array $opts = [], string $default = ''): string
	{
		return $this->get_sel('_POST', $var, $opts, $default);
	}

	public function get_sel(string $where, string $var = 'arg1', array $opts = [], string $default = ''): string
	{
		return in_array($sel = $this->get_var($where, $var), $opts) ? $sel : $default;
	}

//int var val array
//int|var|val
	public function get_array_from_get(string $var, array $default = [], string $filter = 'filter_val'): array
	{
		return $this->get_array('_GET', $var, $default, $filter);
	}

	public function get_array_from_post(string $var, array $default = [], string $filter = 'filter_val'): array
	{
		return $this->get_array('_POST', $var, $default, $filter);
	}

	protected function get_array(string $where, string $var, array $default = [], string $filter = 'filter_val'): array
	{
		$return = $this->get_single_level_array($where, $var, $default);

		foreach ($return as $i => $item) {
			if (!is_string($item)) {
				unset($return[$i]);
			}
		}

		return array_map([$this, $filter], array_values($return));
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

		return $this->ua;
	}

	public function get_ip(): string
	{
		if (is_null($this->ip)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$this->set_ip($_SERVER['HTTP_X_FORWARDED_FOR']);
			} else {
				$this->set_ip($this->_SERVER['REMOTE_ADDR'] ?? '');
			}
		}

		return $this->ip;
	}

	public function get_ref(): string
	{
		if (is_null($this->ref)) {
			//TODO: if proxy
			$this->set_ref($this->_SERVER['HTTP_REFERER'] ?? '');
		}

		return $this->ref;
	}

	public function get_method(): string
	{
		if (is_null($this->method)) {
			$this->set_method($this->_SERVER['REQUEST_METHOD'] ?? '');
		}

		return $this->method;
	}

	public function get_path(): string
	{
		if (is_null($this->path)) {
			$this->set_path($this->_SERVER['REDIRECT_URL'] ?? '');
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
			//TODO: or accept application/json header
			$is_ajax = (strtolower((string)filter_var(@$this->_SERVER['HTTP_X_REQUESTED_WITH'])) === 'xmlhttprequest');
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

	protected function filter_int(mixed $var, /*, array $opts*/int $default, int $min_range): int
	{
		return (int)filter_var($var, FILTER_SANITIZE_NUMBER_INT, /*$opts*/['default' => $default, 'min_range' => $min_range]);
	}

	protected function get_single_level_array(string $where, string|int $var, array $default): array
	{
		if (isset($this->$where[$var])) {
			return array_values((array)$this->$where[$var]);
		} else {
			return $default;
		}
	}
}
