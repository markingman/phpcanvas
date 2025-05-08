<?php

namespace PHPCanvas;

use GuzzleHttp\Client;
use tidy;

class TestHTTPClient
{
	protected Client $client;

	public function get(string $url, array $options = []): array
	{
		return $this->request('GET', $url, $options);
	}

	public function post(string $url, array $options = []): array
	{
		return $this->request('GET', $url, $options);
	}

    public function request(string $method, string $url, array $options = [], $tidy = true): array
    {
        $this->client ??= new Client(['base_uri' => 'http://localhost/']);
        $res = $this->client->request($method, $url, $options);

		$body = (string)$res->getBody();
		if ($tidy) {
			$body = $this->tidy($body);
		}

        return [
            'status' => $res->getStatusCode(),
            'headers' => $res->getHeaders(),
            'body' => $body,
        ];
    }

	public static function tidy(string $html, bool $diagnose = true): string
	{
		$tidy = new tidy();

		if (!$tidy->parseString($html, [
			'drop-empty-elements' => false,
			'indent-spaces' => 4,
			'indent-with-tabs' => true,
			'indent' => true,
			'output-html' => true,
			'tab-size' => 4,
			'wrap' => 2048,
		], 'utf8')) {
			throw new RuntimeException('Failed parsing HTML');
		}

		$output = tidy_get_output($tidy);

		if (!$diagnose) {
			return $output;
		} else {
			$tidy->diagnose();

			return
				$output . PHP_EOL .
				'<!--' . PHP_EOL . trim($tidy->errorBuffer ?? '') . PHP_EOL . '-->';
		}
	}
}
