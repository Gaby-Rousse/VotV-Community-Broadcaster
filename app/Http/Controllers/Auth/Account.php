<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Providers\Functions;
use App\Providers\Queries;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class Account extends Controller
{
    /**
     * Option 1: Disconnect user (flush session + remove cookie)
     * Option 2: Rename your account
     * Option 3: Delete your account and your files
     * @param Request $request
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
        if (strtolower($confirm) == 'y' && $choice == 3) {
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
        if($choice == 2)
        {
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
        }


        if($choice == 4) {
            try {
                $validated = $request->validate([
                    'password' => 'required|string|min:8|confirmed',
                ]);
                User::where('id', Auth::id())->update(['password' => Hash::make($validated['password'])]);
                Auth::logout();
                // Invalidate session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                session()->flash('newUser', 'Password changed. Please re-authenticate');
                return redirect('/signin');
            }
            catch (\Exception $e) {
                session()->flash('newUser', 'Something went wrong:' . $e->getMessage());
                return "Something went wrong:" . $e->getMessage();
            }
        }

        return redirect('/signin');
    }
}
