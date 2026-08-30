<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('channels', function (Blueprint $table) {
        $table->id();
        $table->string('generator_name')->nullable();
        $table->string('radio_url')->nullable();
        $table->string('tv_url')->nullable();
        $table->integer('generator_order')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists('channels');
});

test('generator channels are returned from the database', function () {
    DB::table('channels')->insert([
        [
            'id' => 1,
            'generator_name' => 'Later radio channel',
            'radio_url' => 'https://database.test/later.mp3',
            'tv_url' => null,
            'generator_order' => 2,
        ],
        [
            'id' => 2,
            'generator_name' => 'First radio channel',
            'radio_url' => 'https://database.test/first.mp3',
            'tv_url' => null,
            'generator_order' => 1,
        ],
        [
            'id' => 3,
            'generator_name' => 'TV channel',
            'radio_url' => null,
            'tv_url' => 'https://database.test/tv.mp4',
            'generator_order' => 1,
        ],
        [
            'id' => 4,
            'generator_name' => null,
            'radio_url' => null,
            'tv_url' => null,
            'generator_order' => null,
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
