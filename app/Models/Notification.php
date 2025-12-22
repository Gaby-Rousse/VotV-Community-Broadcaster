<?php

namespace App\Models;

class Notification
{
    public string $created_at;
    public $from;
    public int $to;
    public string $content;
    public int $seen;
    public int $id;


    function __construct($from, int $to, string $content, int $seen = 0)
    {
        $this->from = $from;
        $this->to = $to;
        $this->content = $content;
        $this->seen = $seen;
    }

}
