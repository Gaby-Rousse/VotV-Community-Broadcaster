<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function selfDelete(Request $request)
    {
        $user = $request->user();

        // TODO: delete medias, playlists, etc.

        $user->delete();

        return response()->noContent();
    }
}
