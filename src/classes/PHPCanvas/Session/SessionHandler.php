<?php

namespace PHPCanvas\Session;

class SessionHandler implements \SessionHandlerInterface
{
	protected array $_SESSION;
	private string $dir;
	private string $ref;

	public function init(array $s = null)
	{
		if (is_null($s)) {
			$this->_SESSION =& $_SESSION;
			$this->ref = true;
		} else {
			$this->_SESSION = $s;
			$this->ref = false;
		}
	}

	public function validate(array $a): bool
	{
		// e.g. $a = [REMOTE_ADDR, HTTP_USER_AGENT]

		if (empty($this->_SESSION)) {
			$this->_SESSION['@'] = [];
			foreach ($a as $k => $v) {
				$this->_SESSION['@'][$k] = md5($v);
			}
		} else {
			foreach ($a as $k => $v) {
				if (@$this->_SESSION['@'][$k] !== md5($v)) {
					$this->drop();

					return false;
				}
			}
		}

		return true;
	}

	public function __set($k, $v)
	{
		$this->_SESSION[$k] = $v;
	}

	public function __get($k)
	{
		return @$this->_SESSION[$k];
	}

	public function __isset($k)
	{
		return (null !== @$this->_SESSION[$k]);
	}

	public function __unset($k)
	{
		unset($this->_SESSION[$k]);
		if ($this->ref) {
			unset($_SESSION[$k]);
		}
	}

	public function drop()
	{
		$this->_SESSION = [];
	}

	public function open(string $path, string $name): bool
	{
		$this->dir = $path;

		return true;
	}

	public function close(): bool
	{
		return true;
	}

	public function read(string $id): string
	{
		return (string)@file_get_contents($this->dir . /*'/' . $id[0] .*/ '/' . $id);
	}

	public function write(string $id, string $data): bool
	{
		return (false !== @file_put_contents($this->dir . /*'/' . $id[0] .*/ '/' . $id, $data));
	}

	public function destroy(string $id): bool
	{
		return @unlink($this->dir . /*'/' . $id[0] .*/ '/' . $id);
	}

	public function gc(int $t): int|false
	{
		foreach ((array)glob($this->dir . /*'/**/ '/*') as $f) {
			if ((filemtime($f) + $t) < time()) {
				unlink($f);
			}
		}

		return true;
	}
}
