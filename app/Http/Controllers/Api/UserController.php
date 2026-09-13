<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function delete(Request $request, int $id)
    {
        $user = $request->user();
        if($user->id == $id){
            // TODO: delete medias, playlists, etc.
            $user->delete();

            return response()->noContent();
        }
        else // TODO: admin deletion of another account
            return response()->json(['message' => 'Not implemented'], 501);

    }
}
