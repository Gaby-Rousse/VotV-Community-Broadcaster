<?php

namespace App\Models;
/**
 * A MediaHelper is an object that contains a Media object and multiple attribute to help manipulating it
 */
class MediaHelper
{
public Media $media;
public string $table;
public string $mediaPath;
public string $coverPath;

public function __construct(Media $media, string $table, string $mediaPath, string $coverPath)
{
    $this->media = $media;
    $this->table = $table;
    $this->mediaPath = $mediaPath;
    $this->coverPath = $coverPath;
}
}
