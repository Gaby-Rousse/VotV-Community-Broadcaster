<?php

namespace App\Models;

class Notification
{
    public string $created_at;
    public string $from;
    public string $to;
    public string $content;
    public int $seen;
    public int $id;


    function __construct(string $from, string $to, string $content, int $seen = 0)
    {
        $this->from = $from;
        $this->to = $to;
        $this->content = $content;
        $this->seen = $seen;
    }

}
