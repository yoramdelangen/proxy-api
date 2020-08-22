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
        $url = str_replace(' ', '+', $url);

        if (! $url) {
            return ['err' => 'NO_URL'];
        } elseif (! filter_var($url, FILTER_VALIDATE_URL)) {
            return ['err' => 'INVALID_NO_URL'];
        }

        $client = new \GuzzleHttp\Client([
            'verify' => false,
            'curl' => [CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false]
        ]);

        try {
            $rsp = $client->get($_GET['url']);
        } catch (Exception $e) {
            throwResponseHeader($e->getCode());

            return ['err' => $e->getMessage()];
        }

        $content = (string) $rsp->getBody();
        if ($_GET['force_https'] ?? false) {
            $content = $this->unlocalizeAssets($content, $_GET['url']);
        }

        // when having a query and attr query_string set.
        if (($query = ($_GET['query'] ?? false)) && ($attr = $_GET['attr'] ?? false)) {
            return $this->scrapeDOM($query, $attr, (string) $rsp->getBody());
        }

        $contentType = $rsp->getHeader('content-type')[0];
        if (!in_array(strtolower($contentType), ['text/html', 'text/html; charset=utf-8'], true)) {
            header('Content-Type: '. $contentType);
        }

        return $content;
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

    // let responseBody = data.toString();

    // // make sure all href and src attributes are absolute
    // let domainRegex = /((?:href|src)\s*=\s*"(.+?)")/gm
    // let singleDomainRegex = /(?:href|src)=\"(.*)\"/g
    // const domainmatches = Array.from(responseBody.matchAll(domainRegex), m => m[0]);

    // for(let match of domainmatches) {
    //     let url = match.replace(/^(?:src|href)\s?=\s?"/g, '').replace(/"/g, '')
    //     let attr = match.match(/^src|href/g).pop()

    //     if (isAbsolute(url)) {
    //         continue;
    //     }

    //     responseBody = responseBody.replace(match, attr +'="'+ absoluteUrl(url)+'"')
    // }

    // function isAbsolute(url) {
    //     return url.includes('http://') || url.includes('https://')
    // }

    // function absoluteUrl(url) {
    //     if (!isAbsolute(url)){
    //         url = protocol+'//'+(hostname +'/'+ url).replace(/\/\//g, '/');
    //     }

    //     return url;
    // }

    protected function unlocalizeAssets(string $body, string $url)
    {
        $domainRegex = '/((?:href|src)\s*=\s*"(.+?)")/m';
        $singleDomainRegex = '/(?:href|src)=\"(.*)\"/g';

        preg_match_all($domainRegex, $body, $matches);
        $matchesCount = \count(reset($matches));

        for ($i = 0; $i < $matchesCount; ++$i) {
            $match = $matches[0][$i];
            preg_match('/^src|href/', $matches[1][$i], $attr);
            $asset = $matches[2][$i];

            if ('http' === mb_substr($asset, 0, 4) || '//' === mb_substr($asset, 0, 2) || 'data:' === mb_substr($asset, 0, 5)) {
                continue;
            }

            $body = str_replace($match, reset($attr).'="'.$this->makeAbsolute($url, $asset).'"', $body);
        }

        return $body;
    }

    protected function makeAbsolute(string $url, string $path)
    {
        $domain = parse_url($url);

        // check if is current relative path
        if (mb_substr($path, 0, 2) === './') {
            $path = str_replace('./', $domain['path'], $path);
        }

        return 'https://'.str_replace('//', '/', $domain['host'].'/'.$path);
    }
}
