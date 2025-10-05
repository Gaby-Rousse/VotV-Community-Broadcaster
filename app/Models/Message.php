<?php

namespace App\Models;

class Message
{
    public string $content;
    public string $from;
    public int $seen;
    public string $time;
    public $id;

    public function __construct(string $content, string $from, int $seen = 0, $time = '', $id = null)
    {
        $this->content = $content;
        $this->from = $from;
        $this->seen = $seen;
        $this->time = $time;
        $this->id = $id;
    }
}
