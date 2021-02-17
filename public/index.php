<?php

require_once __DIR__.'/../vendor/autoload.php';

use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\RouteCollector;

$router = new RouteCollector();

$router->get('/', function () {
	return ['msg' => 'There is nothing here for you to find.'];
});

require_once __DIR__.'/../routes/web.php';
require_once __DIR__.'/../routes/webdav.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-TOKEN, Content-Type');

// create dispatcher of the router
$dispatcher = new Dispatcher($router->getData(), new Utils\ResolveHanlder($router));
try {
	$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
} catch (Exception $e) {
	$response = [
		'status' => false,
		'err' => $e->getMessage(),
	];

	if (getenv('DEBUG')) {
		$response['file'] = $e->getFile();
		$response['file_line'] = $e->getLine();
		$response['trace'] = $e->getTrace();
	}
}

// in case the response wasn't an error
if (!is_array($response)) {
	die($response);
}

header('Content-Type: application/json');

echo json_encode($response);
