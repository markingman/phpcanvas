<?php

namespace PHPCanvas\Logs;

class LogHandler implements LogHandlerInterface
{
	protected LogWriteInterface $Out;
	protected string $type;

	public function __construct(LogWriteInterface $out, string $type = '')
	{
		$this->Out = $out;
		$this->type = $type;
	}

	public function emerg(string $log): void
	{
		$this->log($log, LOG_EMERG);
	}

	public function alert(string $log): void
	{
		$this->log($log, LOG_ALERT);
	}

	public function crit(string $log): void
	{
		$this->log($log, LOG_CRIT);
	}

	public function err(string $log): void
	{
		$this->log($log, LOG_ERR);
	}

	public function waring(string $log): void
	{
		$this->log($log, LOG_WARNING);
	}

	public function notice(string $log): void
	{
		$this->log($log, LOG_NOTICE);
	}

	public function debug(string $log): void
	{
		$this->log($log, LOG_DEBUG);
	}

	public function log(string $log, int $type = 0): void
	{
		switch ($type) {
			case LOG_EMERG:
				$label = 'EMERGENCY';
				break;
			case LOG_ALERT:
				$label = 'ALERT';
				break;
			case LOG_CRIT:
				$label = 'CRITICAL';
				break;
			case LOG_ERR:
				$label = 'ERROR';
				break;
			case LOG_WARNING:
				$label = 'WARNING';
				break;
			case LOG_NOTICE:
				$label = 'NOTICE';
				break;
			case LOG_INFO:
				$label = 'INFO';
				break;
			case LOG_DEBUG:
				$label = 'DEBUG';
				break;
			default:
				$label = '-';
				$type = 0;
		}

		$this->Out->write(date('c') . "\t$label ($type)\t" . $log, $this->type);
	}
}
