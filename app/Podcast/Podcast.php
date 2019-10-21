<?php

namespace App\Podcast;

class Podcast
{
    public function handle()
    {
        $db = connectDb(env('DB_PODCAST_USER'), env('DB_PODCAST_PASSWORD'), env('DB_PODCAST'));

        $podcasts = $db->table('podcasts')
            ->select(['podcasts.*', 'g.name as guest_name', 'g.image as guest_image'])
            ->join('guests as g', 'g.id', '=', 'podcasts.guest_id')
            ->get();

        $tags = $db->table('tags')->select(['slug', 'title'])->get();

        return [
            'tags' => array_combine(
                array_column($tags, 'slug'),
                array_column($tags, 'title')
            ),
            'data' => array_map(function ($podcast) {
                return [
                    'id' => (int) $podcast['id'],
                    'type' => $podcast['type'],
                    'guest' => [
                        'name' => $podcast['guest_name'],
                        'image' => $podcast['guest_image'],
                    ],
                    'episode' => (int) $podcast['episode'],
                    'title' => $podcast['title'],
                    'tags' => json_decode($podcast['tags'], true),
                    'slug' => $podcast['slug'],
                    'image' => $podcast['image'],
                    'audio_length' => (int) $podcast['audio_length'],
                ];
            }, $podcasts),
        ];
    }
}
