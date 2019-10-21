<?php

require_once __DIR__.'/../vendor/autoload.php';

use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\RouteCollector;

$router = new RouteCollector();
$router->get('/podcast', [App\Podcast\Podcast::class, 'handle']);
$router->get('dbmanager/schema', [App\DBManager\Schema::class, 'handle']);

// create dispatcher of the router
$dispatcher = new Dispatcher($router->getData());
$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo is_array($response) ? json_encode($response) : $response;
