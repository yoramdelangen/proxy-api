<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// include this file before every application load.
Dotenv\Dotenv::create(__DIR__)->load();

use Utils\DB;
use ClanCats\Hydrahon\Builder;
use ClanCats\Hydrahon\Query\Expression;


//
// Helper functions
//

// function env(string $env)
// {
//     return getenv($env);
// }

function storage_path(string $path)
{
    return realpath(realpath(__DIR__.'/./storage/').'/'.$path);
}

function raw(string $arg): Expression
{
    return new Expression($arg);
}

function connectMySQL(string $username, string $password, string $db = null, string $host = 'localhost'): Builder
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

function connectSQLite(string $path, bool $constrains = true, string $prefix = ''): DB
{
    $capsule = new DB;

    $capsule->addConnection([
        'driver' => 'sqlite',
        // 'url' => env('DATABASE_URL'),
        'database' => $path,
        'prefix' => $prefix,
        'foreign_key_constraints' => $constrains,
    ]);
    $capsule->setAsGlobal();

    return  $capsule;

    // $connection = new PDO('sqlite:'.$path);
    // $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // return new Builder('sqlite', function ($query, $queryString, $queryParameters) use ($connection) {
    //     $statement = $connection->prepare($queryString);
    //     $out = $statement->execute($queryParameters);

    //     if (filter_var(getenv('DEBUG', false), FILTER_VALIDATE_BOOLEAN)) {
    //         dump($query, $queryString, $queryParameters, $out, $connection->errorInfo());
    //     }

    //     if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
    //         return $statement->fetchAll(\PDO::FETCH_ASSOC);
    //     }
    // });
}

function throwResponseHeader(int $code)
{
    if (in_array($code, [400, 401, 405, 406, 500, 501, 502, 503, 504, 404, 403], true)) {
        http_response_code($code);
    }
}

function responseHeader(int $code) {
    http_response_code($code);
}
