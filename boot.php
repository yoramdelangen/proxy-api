<?php

use ClanCats\Hydrahon\Builder;

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

function connectDb(string $username, string $password, string $db = null, string $host = 'localhost'): Builder
{
    $connection = new PDO('mysql:host='.$host .($db ? ';dbname='.$db : null), $username, $password);

    return new Builder('mysql', function ($query, $queryString, $queryParameters) use ($connection) {
        $statement = $connection->prepare($queryString);
        $statement->execute($queryParameters);

        if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        }

        if (filter_var(getenv('DEBUG', false), FILTER_VALIDATE_BOOLEAN)) {
            dump($query, $queryString, $queryParameters);
        }
    });
}
