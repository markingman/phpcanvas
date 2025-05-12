<?php declare(strict_types=1);

namespace PHPCanvas;

// use PHPCanvas\Exception\ApplicationException;
// use PHPCanvas\Http\RequestInterface;
// use PHPCanvas\Http\ResponseInterface;
// use PHPCanvas\Routing\DispatchInterface;
use PHPUnit\Framework\TestCase;
use Throwable;

// use RuntimeException;
// use Throwable;

class ExampleTest extends TestCase
{
	protected TestHTTPClient $client;

	protected function setUp(): void
	{
		try {
			$this->client = new TestHTTPClient;
		} catch (Throwable $e) {
			$this->fail('Could not set up HTTP client; ' . $e->getMessage());
		}
	}

	public function testExample(): void
	{
		$response = $this->client->get('');
		$this->assertEquals(<<<__
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>
			Test
		</title>
	</head>
	<body>
		hello, world
	</body>
</html>
<!--
Info: Document content looks like HTML5
No warnings or errors were found.
-->
__, 
		$response['body']);

// 		$http = new TestHttpClient();
// 		$res = $http->request('GET', 'http://localhost/error');
// 		
// 		$this->assertEquals(500, $res['status']);
// 		$this->assertStringContainsString('Internal Server Error', $res['body']);
	}
}
