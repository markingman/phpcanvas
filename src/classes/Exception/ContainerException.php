<?php

namespace PHPCanvas\Exception;

use RuntimeException;
use Throwable;

class ContainerException extends RuntimeException
{
	private readonly ContainerError $error;

	public function __construct(
		string $message,
		ContainerError $error,
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

	public function getErrorCode(): ContainerError
	{
		return $this->error;
	}
}
