<?php

use Cocur\Slugify\Slugify;

require_once __DIR__.'/../../vendor/autoload.php';

// Meteor - MiniMongo - https://github.com/radekmie/MiniMongoExplorer/blob/master/extension/lib/inject.js
// Headless chrome @ ubuntu - https://gist.github.com/ipepe/94389528e2263486e53645fa0e65578b

$podcasts = PodcastScraper::get();

print('Found '. count($podcasts) .' podcasts to sync.'."\n");

$db = connectDb(env('DB_PODCAST'), env('DB_PODCAST_USER'), env('DB_PODCAST_PASSWORD'));

$pt = $db->table('podcasts');
$gt = $db->table('guests');

foreach ($podcasts as $podcast) {
	// first lookup the guest if he exists
	$guestHash = hash('sha256', trim($podcast['guest']['name']));
	$guest = $gt->select()->where('hash_name', $guestHash)->one();

	if (!$guest) {
		$gt->insert([
			'hash_name' => $guestHash,
			'name' => trim($podcast['guest']['name']),
			'image' => $podcast['guest']['image'],
		])->execute();

		$guest = $gt->select()->where('hash_name', $guestHash)->one();
	}

	// strip episode number from the title.
	preg_match('/^SDS\s(\d{1,3}):\s(.*)/', $podcast['title'], $matches);

	if ($matches) {
		$podcast['episode'] = (int) $matches[1];
		$podcast['title'] = end($matches);
	} else {
		$podcast['episode'] = null;
	}

	$rec = $pt->select()->where('original_id', $podcast['_id'])->one();

	if ($rec) {
		unset($podcast['id']);
		$pt->update([
			'guest_id' => $guest['id'],
			'type' => $podcast['type'],
			'title' => $podcast['title'],
			'episode' => $podcast['episode'],
			'tags' => json_encode($podcast['tags']),
			'slug' => $podcast['prettyLink'],
			'image' => $podcast['image'],
			'audio_length' => (int) $podcast['audio']['timeLength'],
			'audio_source' => $podcast['audio']['url'],
			'updated_at' => 'CURRENT_TIMESTAMP',
		])
			->where('id', $rec['id'])
			->execute();
		continue;
	}

	$pt->insert([
		'original_id' => $podcast['_id'],
		'guest_id' => $guest['id'],
		'type' => $podcast['type'],
		'title' => $podcast['title'],
		'episode' => $podcast['episode'],
		'tags' => json_encode($podcast['tags']),
		'slug' => $podcast['prettyLink'],
		'image' => $podcast['image'],
		'audio_length' => (int) $podcast['audio']['timeLength'],
		'audio_source' => $podcast['audio']['url'],
	])->execute();
}

$tags = PodcastScraper::tags() ?: [];

print('Ready to sync '. count($tags) .' tags.'."\n");

$slugifier = new Slugify();

$tt = $db->table('tags');
foreach ($tags as $id => $tag) {
	$tt->replace([
		'original_id' => $id,
		'title' => $tag,
		'slug' => $slugifier->slugify($tag),
	])->execute();
}
