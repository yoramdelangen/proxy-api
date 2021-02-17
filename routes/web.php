<?php

$router->get('/bear/tags', [App\BearWriter\BearWriter::class, 'tags']);
$router->get('/bear/notes', [App\BearWriter\BearWriter::class, 'notes']);
$router->get('/bear/files', [App\BearWriter\BearWriter::class, 'files']);
$router->get('/get-new-token', [App\GetNewToken::class, 'handle']);
$router->get('/podcast', [App\Podcast\Podcast::class, 'handle']);
$router->get('/dbmanager/schema', [App\DBManager\Schema::class, 'handle']);
$router->get('/dbmanager/mongodb/{db}', [App\DBManager\MongoDb::class, 'overview']);
$router->get('/dbmanager/mongodb/{db}/{collection}', [App\DBManager\MongoDb::class, 'overview']);
$router->get('/dbmanager/mongodb/{db}/{collection}/{id}', [App\DBManager\MongoDb::class, 'overview']);
$router->post('/dbmanager/mongodb/{db}/{collection}', [App\DBManager\MongoDb::class, 'store']);
$router->get('/scrape', [App\Scraper::class, 'handle']);
$router->get('/oauth/callback', [App\OAuthCallback::class, 'handle']);
