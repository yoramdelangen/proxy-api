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

function connectDb(string $db, string $username, string $password, string $host = 'localhost'): Builder
{
    $connection = new PDO('mysql:'.$host.'=localhost;dbname='.$db, $username, $password);

    return new Builder('mysql', function ($query, $queryString, $queryParameters) use ($connection) {
        $statement = $connection->prepare($queryString);
        $statement->execute($queryParameters);

        if (filter_var(getenv('DEBUG', false), FILTER_VALIDATE_BOOLEAN)) {
            dump($query, $queryString, $queryParameters);
        }

        if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        }
    });
}
