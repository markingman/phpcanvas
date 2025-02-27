<?php

namespace PHPCanvas\Logs;

class Rotate
{
	protected int $size;
	protected int $count;
	protected string $dir;

	public function __construct(?int $size = null, ?int $count = null)
	{
		$this->size = $size ?: 10 * 1024 * 1024;
		$this->count = $count ?: 10;
	}

	public function rotate(string $file, ?int $size = null, ?int $count = null): void
	{
		$size = $size ?: $this->size;
		$count = $count ?: $this->count;

		if (file_exists($file) and filesize($file) > $size) {
			$archive_name = $this->archive($file);
			$compression_suffix = $this->compress($archive_name);
			$i = 0;
			foreach (array_reverse(glob($this->get_archive_name($file, true) . $compression_suffix)) as $log) {
				if ($i > $count) {
					unlink($log);
				}
				$i++;
			}
		}
	}

	protected function archive($file): string
	{
		$archive_name = $this->get_archive_name($file);
		rename($file, $archive_name);

		return $archive_name;
	}

	protected function compress($file): string
	{
		$suffix = '.bz';
		$bz = bzopen($file . $suffix, 'w');
		bzwrite($bz, file_get_contents($file), filesize($file));
		bzclose($bz);
		unlink($file);

		return $suffix;
	}

	protected function get_archive_name($file, $as_glob = false)
	{
		return dirname($file) . '/' . basename($file, '.log') . '.' . ($as_glob ? '*' : time()) . '.log';
	}
}
