<?php

require_once __DIR__.'/../vendor/autoload.php';

use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\RouteCollector;

$router = new RouteCollector();
$router->group(['prefix' => 'podcast'], function (RouteCollector $router) {
    $router->get('xml', [App\Podcast\Xml::class, 'handle']);
});

// create dispatcher of the router
$dispatcher = new Dispatcher($router->getData());
$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

echo $response;
