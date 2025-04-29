<?php

namespace PHPCanvas\Exception;

use RuntimeException;
use Throwable;

class ApplicationException extends RuntimeException
{
	private readonly ApplicationError $error;

	public function __construct(
		string $message,
		ApplicationError $error,
		int $code = 0,
		?Throwable $previous = null
	) {
		$this->error = $error;
		if ($previous !== null) {
			$message .= '; ' . (
				strlen($previous->getMessage()) > 64
					? substr($previous->getMessage(), 0, 64) . '...'
					: $previous->getMessage()
			);

		}
		parent::__construct($error->value . '; ' . $message, $code, $previous);
	}

	public function getErrorCode(): ApplicationError
	{
		return $this->error;
	}
}
