<?php

namespace App\DBManager;

use GuzzleHttp\json_decode;
use Illuminate\Support\Str;
use MongoDB\Client as MongoClient;
use Symfony\Component\HttpFoundation\Request;

class MongoDb
{
    const DATABASES = ['my_notes'];

    const ACCESS_TOKEN = [
        'A8y$gmCGt#q9W3KwJ$v3mDPJ!E#fY5gr@?d@Fj9h&YCu5Mv@%r&ra9qPa!k22pVv',
    ];

    public function overview(string $database, string $collection = null, string $id = null)
    {
        $client = new MongoClient();
        $available = $this->getAvailableDatabases($client);

        if (!in_array($database, static::DATABASES, true)) {
            throwResponseHeader(404);
            return ['err' => 'Not found'];
        } else if (!in_array($database, $available, true)) {
            responseHeader(204);
            return ['err' => 'Not created', 'data' => []];
        }

        $db = $client->selectDatabase($database);

        // when there isnt an collection, show all collections
        if (!$collection) {
            return [
                'db' => $database,
                'type' => 'collections',
                'data' => array_map(function ($collection) {
                    return [
                        'name' => $collection->getName(),
                        'options' => $collection->getOptions(),
                    ];
                }, iterator_to_array($db->listCollections()))
            ];
        }

        $db = $client->selectCollection($database, $collection);
        if (!$id) {
            return [
                'db' => $database,
                'type' => 'records',
                'collection' => $collection,
                'data' => array_map(function ($record) {
                    $data = $record->getArrayCopy();
                    unset($data['_id']);
                    return $data;
                }, iterator_to_array($db->find()))
            ];
        }

        $record = $db->findOne(['id' => $id]);
        unset($record['_id']);

        return [
            'id' => $id,
            'db' => $database,
            'type' => 'records',
            'collection' => $collection,
            'data' => $record
        ];
    }

    public function store(string $database, string $collection, string $id = null)
    {
        $client = new MongoClient();
        $available = $this->getAvailableDatabases($client);

        if (!in_array($database, static::DATABASES, true)) {
            throwResponseHeader(404);
            return ['err' => 'Not found'];
        }

        $db = $client->selectCollection($database, $collection);

        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $id = $id ?: ($data['id'] ?? null);

        if ($id && $db->findOne(['id' => $id])) {
            $db->updateOne(['id' => $id], ['$set' => $data]);
        } else {
            if ($id) {
                $data['id'] = $id ?: $data['id'];
            } else if (($data['id'] ?? false) === false) {
                $id = $data['id'] = Str::uuid();
            }

            $db->insertOne($data);
        }

        $record = $db->findOne(['id' => $id]);
        unset($record['_id']);

        return [
            'data' => $record,
        ];
    }

    protected function getAvailableDatabases(MongoClient $client): array
    {
        // generate a list of database
        $available = array_map(function ($name) {
            if (in_array($name, ['admin', 'config', 'local'], true)) {
                return null;
            }
            return $name;
        }, iterator_to_array($client->listDatabaseNames()));

        return array_values(array_filter($available));
    }
}
