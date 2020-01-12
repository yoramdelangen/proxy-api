<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use ClanCats\Hydrahon\Builder;
use ClanCats\Hydrahon\Query\Expression;

// include this file before every application load.
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load(true);

//
// Helper functions
//

function env(string $env)
{
    return getenv($env);
}

function storage_path(string $path)
{
    return realpath(realpath(__DIR__.'/./storage/').'/'.$path);
}

function raw(string $arg): Expression
{
    return new Expression($arg);
}

function connectDb(string $username, string $password, string $db = null, string $host = 'localhost'): Builder
{
    $connection = new PDO('mysql:host='.$host .($db ? ';dbname='.$db : null), $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return new Builder('mysql', function ($query, $queryString, $queryParameters) use ($connection) {
        $statement = $connection->prepare($queryString);
        $out = $statement->execute($queryParameters);

        if (filter_var(getenv('DEBUG', false), FILTER_VALIDATE_BOOLEAN)) {
            dump($query, $queryString, $queryParameters, $out, $connection->errorInfo());
        }

        if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        }
    });
}

function throwResponseHeader(int $code)
{
    if (in_array($code, [400, 401, 405, 406, 500, 501, 502, 503, 504, 404, 403], true)) {
        http_response_code($code);
    }
}
