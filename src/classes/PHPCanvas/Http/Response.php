<?php

namespace PHPCanvas\Http;

class Response implements ResponseInterface
{
	protected $response_code = 200;
	protected $char_set = 'UTF-8';
	protected $headers = [];
	protected $cookies = [];

	public function set_char_set($set)
	{
		$this->char_set = $set;
	}

	public function set_response_code($code)
	{
		$this->response_code = $code;
	}

	public function set_header($key, $value)
	{
		$this->headers[$key] = $value;
	}

	public function unset_header($key)
	{
		unset($this->headers[$key]);
	}

	public function set_cookie($key, $value)
	{
		$this->cookies[$key] = $value;
	}

	public function unset_cookie($key)
	{
		unset($this->cookies[$key]);
	}

	public function html(string $html)
	{
		$this->set_header('Content-Type', sprintf('text/html; charset=%s', $this->char_set));
		$this->respond($html);
	}

	public function text(string $string)
	{
		$this->set_header('Content-Type', sprintf('text/plain; charset=%s', $this->char_set));
		$this->respond($string);
	}

	public function json(string $json)
	{
		$this->set_header('Content-Type', sprintf('text/javascript; charset=%s', $this->char_set));
		$this->respond($json);
	}

	public function file(string $file, $unlink_file = true)
	{
		$this->set_header('Content-Type', sprintf('%s; charset=%s', mime_content_type($file), $this->char_set));
		$this->set_header('Content-Disposition', sprintf('attachment;filename=%s', basename($file)));
		$this->set_header('Content-Length', strval(filesize($file)));//careful of gzip here
		$this->respond($file, true, $unlink_file);
	}

	public function respond($content = '', $read_file = false, $unlink_file = false, $remove_headers = true)
	{
		http_response_code($this->response_code);

		if ($remove_headers) {
			header_remove();
		}

		foreach ($this->headers as $key => $value) {
			header($key . ': ' . $value);
		}

		foreach ($this->cookies as $cookie) {
			//@todo: setcookie(name, value, expire, path, domain, secure, httponly);
		}

		if (!$read_file) {
			echo $content;
		} else {
			readfile($content);
			if ($unlink_file) {
				unlink($content);
			}
		}

		exit;
	}
}
