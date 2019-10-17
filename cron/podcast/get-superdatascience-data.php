<?php

require_once __DIR__.'/../../vendor/autoload.php';

// https://github.com/radekmie/MiniMongoExplorer/blob/master/extension/lib/inject.js

$podcasts = PodcastScraper::get();

$db = connectDb(env('DB_PODCAST'), env('DB_PODCAST_USER'), env('DB_PODCAST_PASSWORD'));

$pt = $db->table('podcasts');
$gt = $db->table('guests');

foreach ($podcasts as $podcast) {
	// first lookup the guest if he exists
	$guestHash = hash('sha256', $podcast['guest']['name']);
	$guest = $gt->select()->where('hash_name', $guestHash)->one();
	dump($guest);
	if (!$guest) {
		$gt->insert([
			'hash_name' => $guestHash,
			'name' => $podcast['guest']['name'],
			'image' => $podcast['guest']['image'],
		])->execute();

		$guest = $gt->select()->where('hash_name', $guestHash)->one();
	}

	dd($podcast, $guest);
	$rec = $pt->select()->where('id', $podcast['_id'])->one();

	if ($rec) {
		dd($rec);
	}
}

die;
sleep(10);

// [
// 	"_id" => "wXQjYQATDqHrzhKmj"
// 	"audio" => array:2 [ …2]
// 	"creationDate" => []
// 	"guest" => array:2 [ …2]
// 	"image" => "https://sds-platform-private.s3-us-east-2.amazonaws.com/podcast-images/uvP4znMn2jYzQ4anS"
// 	"prettyLink" => "sds-031-ab-testing-kissmetrics-and-ways-to-a-better-lifestyle-with-david-tanaskovic"
// 	"tags" => array:2 [ …2]
// 	"title" => "SDS 031: AB Testing, Kissmetrics and ways to a better lifestyle"
// 	"type" => "guest"
// ]
