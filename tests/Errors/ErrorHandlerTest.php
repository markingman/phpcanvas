<?php

namespace PHPCanvas\Errors;

use PHPUnit\Framework\TestCase;
use Exception;
use ErrorException;

class ErrorHandlerTest extends TestCase
{
    protected ErrorHandler $ErrorHandler;

    public function setUp(): void
    {
        $this->ErrorHandler = new ErrorHandler();
        $this->ErrorHandler->set_terminate(false);
    }

    public function testCanCreate(): void
    {
        $this->assertInstanceOf(ErrorHandler::class, $this->ErrorHandler);
    }

    public function testSetView(): void
    {
        $view = function (Exception $e): void {};
        
        $null = $this->ErrorHandler->set_view($view);
        $this->assertNull($null);
    }

    public function testSetLog(): void
    {
        $log = function (Exception $e): void {};
        
        $null = $this->ErrorHandler->set_log($log);
        $this->assertNull($null);
    }

    public function testHandleErrorNone(): void
    {
        $res = $this->ErrorHandler->handle_error(0, 'err', '/tmp/err.php', 1);
        $this->assertFalse($res);
    }

    public function testHandleErrorException(): void
    {
        $res = $this->ErrorHandler->handle_error(E_USER_ERROR, 'ERR_MSG', '/tmp/err.php', 1);
        $this->assertTrue($res);
    }

    public function testHandleException(): void
    {
        $null = $this->ErrorHandler->handle_exception(
			new ErrorException('ERR_MSG', 0, E_ERROR, '/tmp/err.php', 1)
        );
        $this->assertNull($null);
    }

	public function testLog(): void
	{
        $res = $this->ErrorHandler->handle_exception(
			new ErrorException('ERR_MSG', 0, E_ERROR, '/tmp/err.php', 1)
        );
        $this->assertEquals('log');
	}
}
