<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class User
{
    public int $Id;
    public string $username;
    public $password;
    public int $admin;
    public function isAdmin() : bool
    {
        return $this->admin == 1;
    }

    public function notificationCount() : int
    {
        return DB::table('notifications')->where('seen', 0)->where('to', $this->username)->count();
    }

    function __construct(string $username, int $admin = 0, $password = null)
    {
        $this->username = $username;
        $this->admin = $admin;
        $this->password = $password;
    }
}
