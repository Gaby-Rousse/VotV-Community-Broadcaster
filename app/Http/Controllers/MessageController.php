<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Music;
use App\Models\Notification;
use App\Providers\Queries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kiwilan\Audio\Audio;
use Illuminate\Support\Facades\File;
use PhpParser\Lexer\TokenEmulator\ReadonlyTokenEmulator;

class MessageController extends Controller
{
    function suggestions()
    {
        $values = DB::table('suggestions')->orderBy('time', 'desc')->get();
        $messages = [];
        foreach ($values as $value) {
            $messages[] = new Message($value->content, $value->from, $value->seen, $value->time, $value->id);
        }
        return view('message', [
            'title' => 'Suggestions',
            'description' => 'Suggest something or consult current suggestions here!',
            'current' => 'Current Suggestions',
            'table' => 'suggestions',
            'messages' => $messages
        ]);
    }

    function bugs()
    {
        $values = DB::table('bugs')->orderBy('time', 'desc')->get();
        $messages = [];
        foreach ($values as $value) {
            $messages[] = new Message($value->content, $value->from, $value->seen, $value->time, $value->id);
        }
        return view('message', [
            'title' => 'Bugs',
            'description' => 'Report bugs or consult already reported bugs here!',
            'current' => 'Reported Bugs',
            'table' => 'bugs',
            'messages' => $messages
        ]);
    }

    function sendMessage(Request $request)
    {
        if (Auth::check()) {
            $validated = $request->validate([
                'message' => 'required|max:2000',
                'table' => 'required|in:suggestions,bugs'
            ]);
            Queries::insertNotification(new Notification(Auth::user()->username, 'Gaby Rousse', 'added a new ' . substr_replace($validated['table'], "", -1) . '!'));
            Queries::insertMessage(new Message($validated['message'], Auth::user()->username), $validated['table']);
            return redirect($validated['table']);
        }
        return redirect()->back();
    }

    function updateMessage(Request $request)
    {
        if (Auth::check()) {
            $validated = $request->validate([
                'table' => 'required|in:suggestions,bugs'
            ]);
            $deleteId = $request->input('delete');
            $seenId = $request->input('seen');
            $table = $validated['table'];
            if ($deleteId) {
                if (Auth::user()->isAdmin()) {
                    DB::table($table)->where('id', $deleteId)->delete();
                } else {
                    if (Queries::isOwnerOfMessage($deleteId, $table)) {
                        DB::table($table)->where('id', $deleteId)->delete();
                    }
                }

            } else if ($seenId) {
                if (Auth::user()->isAdmin()) {
                    DB::table($table)->where('id', $seenId)->update(['seen' => 1]);
                }
            }
        }
        return redirect()->back();
    }
}
