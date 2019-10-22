<?php

namespace App\DBManager;

use PDO;

class Schema
{
	public function handle()
	{
		$host = 'localhost';
		$connection = new PDO('mysql:host='.$host, env('DB_PODCAST_USER'), env('DB_PODCAST_PASSWORD'));

		//debug connection
		// $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// get a list of all databases.
		$databases = $connection->query('show databases')->fetchAll(PDO::FETCH_ASSOC);
		$databases = array_filter(array_map('reset', $databases), function ($db) {
			return $db !== 'information_schema';
		});

		$database = 'podcast_db';

		// set database X as database in the connection
		$connection->query('use '. $database);

		// get a list of all tables within the database.
		$tables = $connection->query('show tables')->fetchAll(PDO::FETCH_ASSOC);
		$tables = array_map('reset', $tables);

		$table = 'podcasts';
		// solution 1
		$schema = $connection->query('DESCRIBE '.$table)->fetchAll(PDO::FETCH_ASSOC);

		return [
			'database' => $database,
			'tables' => $tables,
			'schema' => [
				'table' => $table,
				'structure' => $schema,
			]
		];
	}
}
