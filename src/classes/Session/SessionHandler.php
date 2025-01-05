<?php

namespace PHPCanvas\Session;

use DirectoryIterator;
use Exception;
use SessionHandlerInterface;

class SessionHandler implements SessionHandlerInterface
{
	/** @var array<string, string|bool|int|array<string, string>|null> */
	protected array $_SESSION = [];
	private string $dir;
	private bool $ref;

	/** @param array<string, string|bool|int|array<string, string>|null> $s */
	public function init(?array $s = null): void
	{
		if (is_null($s)) {
			if (session_status() !== PHP_SESSION_ACTIVE) {
				throw new Exception('SESSION_HANDLER_REF; Cannot reference inactive session');
			}
			$this->_SESSION =& $_SESSION;
			$this->ref = true;
		} else {
			$this->_SESSION = $s;
			$this->ref = false;
		}
	}

	/** @param array<string, string> $a */
	public function validate(array $a): bool
	{
		// e.g. $a = [REMOTE_ADDR, HTTP_USER_AGENT]

		if (empty($this->_SESSION['@']) or !is_array($this->_SESSION['@'])) {
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

	/** @return string|bool|int|array<string, string>|null> */
	public function __get(string $k): string|bool|int|array|null
	{
		return $this->_SESSION[$k] ?? null;
	}

	/** @param string|bool|int|array<string, string>|null $v */
	public function __set(string $k, string|bool|int|array|null $v): void
	{
		$this->_SESSION[$k] = $v;
	}

	public function __isset(string $k): bool
	{
		return isset($this->_SESSION[$k]);
	}

	public function __unset(string $k): void
	{
		unset($this->_SESSION[$k]);
		if ($this->ref and isset($_SESSION[$k])) {
			unset($_SESSION[$k]);
		}
	}

	public function drop(): void
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

	public function read(string $id): string|false
	{
		return file_get_contents($this->dir . /*'/' . $id[0] .*/ '/' . $id);
	}

	public function write(string $id, string $data): bool
	{
		return (false !== file_put_contents($this->dir . /*'/' . $id[0] .*/ '/' . $id, $data));
	}

	public function destroy(string $id): bool
	{
		return @unlink($this->dir . /*'/' . $id[0] .*/ '/' . $id);
	}

	public function gc(int $max_lifetime): int|false
	{
		$i = 0;

		foreach (new DirectoryIterator($this->dir) as $it) {
			if ($it->isDot() or $it->isDir()) {
				continue;
			}

			if ((filemtime($it->getPathname()) + $max_lifetime) < time()) {
				if (unlink($it->getPathname())) {
					$i++;
				} else {
					return false;
				}
			}
		}

		return $i;
	}
}
