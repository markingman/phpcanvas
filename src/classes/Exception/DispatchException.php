<?php declare(strict_types=1);

namespace PHPCanvas\Exception;

use RuntimeException;
use Throwable;

class DispatchException extends RuntimeException
{
	private readonly DispatchError $error;

	public function __construct(
		string $message,
		DispatchError $error,
		int $code = 500,
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

	public function getErrorCode(): DispatchError
	{
		return $this->error;
	}
}
