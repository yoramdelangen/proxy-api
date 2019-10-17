<?php

namespace App\Podcast;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Xml
{
    public function handle()
    {
        header('content-type', 'text/xml');
        header('Access-Control-Allow-Origin', '*');

        $cache = new FilesystemAdapter();
        $items = $cache->get('podcast', function (ItemInterface $item) {
            $item->expiresAfter(3600);

            $client = new GuzzleHttp\Client();
            $rsp = $client->get('https://feeds.soundcloud.com/users/soundcloud:users:253585900/sounds.rss');

            return (string) $rsp->getBody();
        });

        return $items;
    }
}
