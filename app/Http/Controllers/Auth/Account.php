<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Account extends Controller
{
    /**
     * Option 1: Disconnect user (flush session + remove cookie)
     * Option 2: Rename your account
     * Option 3: Delete your account and your files
     * @param Request $request
     * @return Redirector|RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $choice = $request->input('choice');

        $confirm = $request->input('confirm');
        if ($choice == 1) {
            Auth::logout();

            // Invalidate session
            $request->session()->invalidate();

            $request->session()->regenerateToken();
            return redirect('/');
        }
        if (strtolower($confirm) == 'y') {
            /*$temp_audios = DB::table('audios')->where('approved', '=', 0)->where('owner', Auth::user()->username)->get();
            foreach ($temp_audios as $values) {
                unlink(public_path('temp_uploads/musics/') . $values->filename);
            }
            $audios = DB::table('audios')->where('approved', '=', 1)->where('owner', Auth::user()->username)->get();
            foreach ($audios as $values) {
                unlink(public_path('uploads/musics/') . $values->filename);
            }*/
            Auth::logout();
            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            session()->flash('newUser', 'Account deleted successfully. Goodbye.');
            return Redirect('/signin');
        }
        $validated = $request->validate([
            'username' => 'required|unique:users|max:20'
        ]);
        if ($validated['username']) {
            User::where('id', Auth::id())->update(['username' => $validated['username']]);
            Auth::logout();
            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            session()->flash('newUser', 'Account renamed, please re-authenticate');
            return Redirect('/signin');
        }
        return redirect('/account');
    }
}
