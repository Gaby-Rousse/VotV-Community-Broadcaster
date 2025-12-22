<?php

namespace App\Models;

use App\Providers\Functions;
use Illuminate\Support\Facades\DB;

class Media
{
    //Insert/Update
    public string $filename;
    public string $title;
    public string $artist;
    public string $cover;
    public string $album;
    public string $genre;
    public string $year;
    public string $description;

    public string $type;
    public string $destination;
    public int $ownerId;

    //Get
    public int $approved = 0;
    public int $explicit;
    public string $seconds;
    public bool $isPending;
    public bool $isFavorite;
    public int $favoriteCount;
    public bool $isReported;
    public string $reportReason;

    function __construct(string $filename, string $title, string $artist, string $cover, string $album, string $genre, string $year, string $description, string $type, int $owner, string $destination = 'None')
    {
        $this->filename = $filename;
        $this->title = $title;
        $this->artist = $artist;
        $this->cover = $cover;
        $this->album = $album;
        $this->genre = $genre;
        $this->year = $year;
        $this->description = $description;
        $this->type = $type;
        $this->ownerId = $owner;
        $this->destination = $destination;
    }

}
