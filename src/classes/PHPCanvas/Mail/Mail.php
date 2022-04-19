<?php
/**
 * This file is part of PHPCanvas
 *
 * $Id: Mail.php 823 2020-09-23 22:55:23Z mark $
 *
 * @copyright   Copyright (C) 2016 Mark Ingman, Valhalla Software
 * @license     http://phpcanvas.org/liecnse
 */

namespace PHPCanvas\Mail;

use Exception;
use PHPCanvas\Logs\LogHandlerInterface;

class Mail implements MailInterface
{
	protected $LogHandler;
	public $mail_sender;
	protected $active;
	protected $regx;
	public $log_mbx_name = 'mail_log_dev.mbx';

	function __construct(LogHandlerInterface $LogHandler, $mail_sender, $active = false, $regx = '')
	{
		$this->LogHandler = $LogHandler;
		$this->mail_sender = $mail_sender;
		$this->active = $active;
		$this->regx = $regx;
	}

	public function send($to, $subject = '', $message = '', $headers = '', $params = '', $log = '', $test = false)
	{
		if (!$test and ($this->is_active() or (strlen($this->regx) and preg_match($to, $this->regx)))) {
			if (strlen($headers)) {
				$headers = $this->normalise_eol($headers);
			}
			if ($params) {
				$sent = @mail($to, $subject, $message, $headers, $params);
			} elseif ($headers) {
				$sent = @mail($to, $subject, $message, $headers);
			} else {
				$sent = @mail($to, $subject, $message);
			}

			if (!$sent) {
				throw new Exception("Email not sent to $to");
			}

			if (strlen($log)) {
				$this->log($to, $subject, $message, $headers, $params, $log);
			}

			return $sent;
		} else {
			return $this->log($to, $subject, $message, $headers, $params, $this->log_mbx_name);//Quiet if dev mode
		}
	}

	public function is_active()
	{
		return $this->active;
	}

	public function log($to, $subject = '', $message = '', $headers = '', $params = '', $log = 'mail_log.mbx')
	{
		$time = time();
		$this->LogHandler->add(
			$log,
			"From <" . $this->mail_sender . "> " . date('D M d H:i:s Y', $time) . PHP_EOL .
			"To: $to" . PHP_EOL .
			($headers ? $headers = $this->normalise_eol($headers, PHP_EOL) : '') .
			"Subject: $subject" . PHP_EOL .
			"Date: " . date('D d M Y H:i:s', $time) . " (time at server)" . PHP_EOL .
			PHP_EOL .
			trim($this->normalise_eol($message, PHP_EOL)) . PHP_EOL//"PHP_EOL" <-- $this->LogHandler->add() writes one more EOL here
		);

		return true;
	}

	public function normalise_eol($string, $eol = "\n")
	{
		return preg_replace('~\R~u', $eol, $string);
	}
}
