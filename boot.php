<?php

use ClanCats\Hydrahon\Builder;

// include this file before every application load.
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

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

function connectDb(string $db, string $username, string $password, string $host = 'localhost'): Builder
{
    $connection = new PDO('mysql:'.$host.'=localhost;dbname='.$db, $username, $password);

    return new Builder('mysql', function ($query, $queryString, $queryParameters) use ($connection) {
        $statement = $connection->prepare($queryString);
        $statement->execute($queryParameters);

        if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        }

        dd(__METHOD__, $query, $queryString, $queryParameters);
    });
}
