<?php

namespace App;

use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;

class Scraper
{
	// const ACCESS_TOKEN = [
	// 	'dnvqGHjfBk8S#tSJEGaG4$4xf*SKv&2VrK#v3&Z55ezzTDZk%r5ta?WU67&ySpb@"'
	// ];

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
			return ['err' => $e->getMessage()];
		}

		// when having a query and attr query_string set.
		if (($query = ($_GET['query'] ?? false)) && ($attr = $_GET['attr'] ?? false)) {
			return $this->scrapeDOM($query, $attr, (string) $rsp->getBody());
		}

		return (string) $rsp->getBody();
	}

	protected function scrapeDOM(string $query, string $selector, string $html): array
	{
		$crawler = new Crawler($html);

		// try to convert given input, when its invalid, it was already XPATH?
		try {
			$query = (new CssSelectorConverter())->toXPath('div#subbed-Animegg iframe[src]');
		} catch (SyntaxErrorException $e) {
			// noop
		}

		$filter = $crawler->filterXPath($query)->attr($selector);

		return [
			'selector' => $selector,
			'result' => $filter,
		];
	}
}
