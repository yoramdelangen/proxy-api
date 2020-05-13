<?php

namespace App\BearWriter;

use Utils\DB;

class BearWriter
{
	const DEV_PATH = '/Users/yoram/Library/Group Containers/9K33E3U3T4.net.shinyfrog.bear/Application Data/database.sqlite';
	const PROD_PATH = '/var/www/apps/bear-writer/database.sqlite';

    const ACCESS_TOKEN = [
        'StXDQgBRtcBcxTbwnQs#vM@CQYd%E54B$FHHYaRB42GGzG!yr%fjCMn9x%U3F5hU',
    ];

	public function notes()
	{
		$timer = microtime(true);

		$databasePath = getenv('APP_ENV') === 'local' ? static::DEV_PATH : static::PROD_PATH;

		// check if file exists
		if (!file_exists($databasePath)) {
			return ['err' => 'No there yet'];
		}

		connectSQLite($databasePath);

		$tags = DB::table('ZSFNOTETAG')
			->join('Z_7TAGS', 'Z_7TAGS.Z_14TAGS', '=', 'ZSFNOTETAG.Z_PK')
			->select('Z_7TAGS.Z_7NOTES as note_id', 'ZTITLE as label'/*, 'ZMODIFICATIONDATE as created_at'*/)
			->get()
			->groupBy('note_id')
			->map
				->pluck('label')
			->toArray();

		$files = DB::table('ZSFNOTEFILE')
			->get()
			->transform(function($file) {
				return [
					'id' => $file->ZUNIQUEIDENTIFIER,
					'pk_id' => (int) $file->Z_PK,
					'note_id' => (int) $file->ZNOTE,
					'filename' => $file->ZFILENAME,
					'filesize' => (int) $file->ZFILESIZE,
					'extension' => $file->ZNORMALIZEDFILEEXTENSION,
					'height' => (int) $file->ZHEIGHT,
					'width' => (int) $file->ZWIDTH,
					'is_deleted' => (bool) $file->ZPERMANENTLYDELETED,
					'uploaded_at' => date('Y-m-d H:i:s', $file->ZUPLOADEDDATE)
				];
			})
			->groupBy('note_id')
			->toArray();

		$notes = DB::table('ZSFNOTE')
			->orderByDesc('ZMODIFICATIONDATE')
			->get()
			->transform(function ($note) use ($tags, $files) {
				return [
					'id' => $note->ZUNIQUEIDENTIFIER,
					'pk_id' => (int) $note->Z_PK,
					'title' => $note->ZTITLE,
					'markdown' => $note->ZTEXT,
					'tags' => $tags[(int) $note->Z_PK] ?? [],
					'files' => $files[(int) $note->Z_PK] ?? [],
					'deleted_at' => $note->ZTRASHEDDATE ? date('Y-m-d H:i:s', $note->ZTRASHEDDATE) : null,
					'created_at' => date('Y-m-d H:i:s', $note->ZCREATIONDATE),
					'updated_at' => date('Y-m-d H:i:s', $note->ZMODIFICATIONDATE),
				];
			});

		return [
			'notes' => $notes->toArray(),
			'total' => $notes->count(),
			'timer' => round(microtime(true) - $timer, 4).'s',
		];
	}

	public function tags()
	{
		$timer = microtime(true);

		$databasePath = getenv('APP_ENV') === 'local' ? static::DEV_PATH : static::PROD_PATH;

		// check if file exists
		if (!file_exists($databasePath)) {
			return ['err' => 'No there yet'];
		}

		connectSQLite($databasePath);


		$tags = DB::table('ZSFNOTETAG')
			->join('Z_7TAGS', 'Z_7TAGS.Z_14TAGS', '=', 'ZSFNOTETAG.Z_PK')
			->select('Z_7TAGS.Z_7NOTES as note_id', 'ZSFNOTETAG.Z_PK as id', 'ZTITLE as label', 'ZMODIFICATIONDATE as created_at')
			->get()
			->groupBy('id')
			->map(function ($tags) {
				$tag = $tags->first();
				$tag->created_at = date('Y-m-d H:i:s', $tag->created_at);

				$tag->notes = array_map('intval', $tags->pluck('note_id')->toArray());

				return $tag;
			});

		return [
			'tags' => $tags->values()->toArray(),
			'total' => $tags->count(),
			'timer' => round(microtime(true) - $timer, 4).'s',
		];
	}

	public function files()
	{
		$timer = microtime(true);

		$databasePath = getenv('APP_ENV') === 'local' ? static::DEV_PATH : static::PROD_PATH;

		// check if file exists
		if (!file_exists($databasePath)) {
			return ['err' => 'No there yet'];
		}

		connectSQLite($databasePath);

		$files = DB::table('ZSFNOTEFILE')
			->get()
			->transform(function($file) {
				return [
					'id' => $file->ZUNIQUEIDENTIFIER,
					'pk_id' => (int) $file->Z_PK,
					'note_id' => (int) $file->ZNOTE,
					'filename' => $file->ZFILENAME,
					'filesize' => (int) $file->ZFILESIZE,
					'extension' => $file->ZNORMALIZEDFILEEXTENSION,
					'height' => (int) $file->ZHEIGHT,
					'width' => (int) $file->ZWIDTH,
					'is_deleted' => (bool) $file->ZPERMANENTLYDELETED,
					'uploaded_at' => date('Y-m-d H:i:s', $file->ZUPLOADEDDATE)
				];
			});

		return [
			'files' => $files->toArray(),
			'total' => $files->count(),
			'timer' => round(microtime(true) - $timer, 4).'s',
		];
	}
}
