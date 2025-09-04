<?php declare(strict_types=1);

namespace PHPCanvas;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
	protected TestHTTPClient $client;

	protected function setUp(): void
	{
		$this->client = new TestHTTPClient;
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
