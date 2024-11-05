<?php

namespace PHPCanvas\Http;

class Response implements ResponseInterface
{
	protected bool $terminate_after_response = true;
	protected int $response_code = 200;
	protected string $char_set = 'UTF-8';
	/** @var array<string> $headers */
	protected array $headers = [];
	/** @var array<string, Cookie> $cookies */
	protected array $cookies = [];

	public function __construct(bool $terminate_after_response = true)
	{
		$this->set_terminate_after_response($terminate_after_response);
	}

	public function set_terminate_after_response(bool $terminate_after_response): void
	{
		$this->terminate_after_response = $terminate_after_response;
	}

	public function set_char_set(string $set): void
	{
		$this->char_set = $set;
	}

	public function set_response_code(int $code): void
	{
		$this->response_code = $code;
	}

	public function set_header(string $key, string $value): void
	{
		$this->headers[$key] = $value;
	}

	public function unset_header(string $key): void
	{
		unset($this->headers[$key]);
	}

	public function set_cookie(
		string $name,
		string $value = '',
		int $expires = 0,
		string $path = '',
		string $domain = '',
		bool $secure = false,
		bool $httponly = false,
    ): void
	{
		$this->cookies[$name] = new Cookie(
			value: $value,
			expires: $expires,
			path:  $path,
			domain: $domain,
			secure: $secure,
			httponly: $httponly,
		);
	}

	public function unset_cookie(string $key): void
	{
		unset($this->cookies[$key]);
	}

	public function html(string $html): void
	{
		$this->set_header('Content-Type', sprintf('text/html; charset=%s', $this->char_set));
		$this->respond($html);
	}

	public function text(string $text): void
	{
		$this->set_header('Content-Type', sprintf('text/plain; charset=%s', $this->char_set));
		$this->respond($text);
	}

	public function json(string $json): void
	{
		$this->set_header('Content-Type', sprintf('text/javascript; charset=%s', $this->char_set));
		$this->respond($json);
	}

	public function file(string $file, bool $unlink_file = true): void
	{
		$this->set_header('Content-Type', sprintf('%s; charset=%s', mime_content_type($file), $this->char_set));
		$this->set_header('Content-Disposition', sprintf('attachment;filename=%s', basename($file)));
		$this->set_header('Content-Length', strval(filesize($file)));//careful of gzip here
		$this->respond($file, true, $unlink_file);
	}

	public function redirect(string $to, int $code = 303): void
	{
		$this->set_response_code($code);
		$this->set_header('Location', $to);
		$this->respond(null);
	}

	public function respond(?string $content = '', bool $read_file = false, bool $unlink_file = false, bool $remove_headers = true): void
	{
		http_response_code($this->response_code);

		if ($remove_headers) {
			header_remove();
		}

		foreach ($this->headers as $key => $value) {
			header($key . ': ' . $value);
		}

		foreach ($this->cookies as $name => $cookie) {
			setcookie($name, $cookie->value, $cookie->expires, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httponly);
		}

		if (!is_null($content)) {
			if (!$read_file) {
				echo $content;
			} else {
				readfile($content);
				if ($unlink_file) {
					unlink($content);
				}
			}
		}

		$this->terminate();
	}

	protected function terminate(): void
	{
		if ($this->terminate_after_response) {
			exit;// @codeCoverageIgnore
		}
	}
}
