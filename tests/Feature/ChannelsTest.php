<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('channels', function (Blueprint $table) {
        $table->id();
        $table->string('display_name');
        $table->string('playlist_name')->nullable();
        $table->string('long_name')->nullable();
        $table->string('url')->nullable();
        $table->string('category');
        $table->string('kind')->default('assigned');
        $table->integer('icecast_index')->nullable();
        $table->integer('sort_order')->default(0);
    });

    DB::statement("CREATE VIEW browse_audio AS SELECT * FROM channels WHERE category = 'audio'");
    DB::statement("CREATE VIEW browse_video AS SELECT * FROM channels WHERE category = 'video'");
});

afterEach(function () {
    DB::statement('DROP VIEW IF EXISTS browse_audio');
    DB::statement('DROP VIEW IF EXISTS browse_video');
    Schema::dropIfExists('channels');
});

test('generator channels are returned from the database', function () {
    DB::table('channels')->insert([
        [
            'id' => 1,
            'display_name' => 'Later radio channel',
            'long_name' => 'Later radio channel',
            'url' => 'https://database.test/later.mp3',
            'category' => 'audio',
            'sort_order' => 2,
        ],
        [
            'id' => 2,
            'display_name' => 'First radio channel',
            'long_name' => 'First radio channel',
            'url' => 'https://database.test/first.mp3',
            'category' => 'audio',
            'sort_order' => 1,
        ],
        [
            'id' => 3,
            'display_name' => 'TV channel',
            'long_name' => 'TV channel',
            'url' => 'https://database.test/tv.mp4',
            'category' => 'video',
            'sort_order' => 1,
        ],
        [
            'id' => 4,
            'display_name' => 'Hidden channel',
            'long_name' => null,
            'url' => null,
            'category' => 'event',
            'sort_order' => 1,
        ],
    ]);

    $this->getJson('/api/v1/channels')
        ->assertOk()
        ->assertExactJson([
            'radio' => [
                [
                    'id' => 2,
                    'name' => 'First radio channel',
                    'url' => 'https://database.test/first.mp3',
                ],
                [
                    'id' => 1,
                    'name' => 'Later radio channel',
                    'url' => 'https://database.test/later.mp3',
                ],
            ],
            'tv' => [
                [
                    'id' => 3,
                    'name' => 'TV channel',
                    'url' => 'https://database.test/tv.mp4',
                ],
            ],
        ]);
});
