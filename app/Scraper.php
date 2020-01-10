<?php

namespace App;

use Exception;

class Scraper
{
	const ACCESS_TOKEN = [
		'dnvqGHjfBk8S#tSJEGaG4$4xf*SKv&2VrK#v3&Z55ezzTDZk%r5ta?WU67&ySpb@"'
	];

	public function handle()
	{
		$url = $_GET['url'] ?? null;

		if (!$url) {
			return ['err' => 'NO_URL'];
		} elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
		    return ['err' => 'INVALID_NO_URL'];
		}

		$client = new \GuzzleHttp\Client();
		try {
			$rsp = $client->get($_GET['url']);
		} catch (Exception $e) {
			return ['err' => $e->getMessge()];
		}

		return (string) $rsp->getBody();
	}
}
