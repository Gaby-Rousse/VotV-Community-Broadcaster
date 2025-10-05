<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Models\User;
use App\Providers\Queries;
use Hash;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kiwilan\Audio\Audio;

class UserController extends Controller
{

    /**
     * Creates a new User
     * @param Request $request
     * @return Application|RedirectResponse|Redirector|object
     */
    function signup(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users|max:20',
            'password' => 'required|confirmed|min:8',
        ]);
        $user = new User($validated['username'], 0, Hash::make($validated['password']));
        Queries::insertUser($user);
        session()->flash('newUser', 'Account created successfully, please sign in');
        return Redirect('/signin');

    }

    /**
     * Option 1: Disconnect user (flush session + remove cookie)
     * Option 2: Rename your account
     * Option 3: Delete your account and your files
     * @param Request $request
     * @return Application|RedirectResponse|Redirector|object
     */
    function account(Request $request)
    {
        $choice = $request->input('choice');

        $confirm = $request->input('confirm');
        if ($choice == 1) {
            session()->flush();
            //https://stackoverflow.com/questions/23063240/laravel-cookieforget-doesnt-work
            //toujours utiliser Queue pour une raison qui m'échappe
            Cookie::queue(Cookie::forget('username'));
            return redirect('/');
        }
        if (strtolower($confirm) == 'y') {
            $temp_audios = DB::table('audios')->where('approved','=',0)->where('owner', session('connectedUser')->username)->get();
            foreach ($temp_audios as $values) {
                unlink(public_path('temp_uploads/musics/') . $values->filename);
            }
            $audios = DB::table('audios')->where('approved','=',1)->where('owner', session('connectedUser')->username)->get();
            foreach ($audios as $values) {
                unlink(public_path('uploads/musics/') . $values->filename);
            }
            DB::table('audios')->where('owner', session('connectedUser')->username)->delete();
            DB::table('users')->where('username', session('connectedUser')->username)->delete();
            DB::table('notifications')->where('to', session('connectedUser')->username)->delete();
            session()->flush();
            Cookie::queue(Cookie::forget('username'));
            session()->flash('newUser', 'Account deleted successfully. Goodbye.');
            return Redirect('/signin');
        }
        $validated = $request->validate([
            'username' => 'required|unique:users|max:20'
        ]);
        if($validated['username'])
        {
            DB::table('audios')->where('owner', session('connectedUser')->username)->update(['owner' => $validated['username']]);
            DB::table('users')->where('username', session('connectedUser')->username)->update(['username' => $validated['username']]);
            DB::table('notifications')->where('to', session('connectedUser')->username)->update(['to' => $validated['username']]);
            session()->flush();
            session()->flash('newUser', 'Account renamed, please re-authenticate');
            return Redirect('/signin');
        }
        return redirect('/account');
    }

    /**
     * Insert User inside the session
     * @param Request $request
     * @return Application|RedirectResponse|Redirector|object
     */
    function signin(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Hash::check($validated['password'], DB::table('users')->where('username', $validated['username'])->value('password'))) {
            $values = DB::table('users')->where('username', $validated['username'])->get();
            $user = new User($values[0]->username, $values[0]->isAdmin);
            Session(['connectedUser' => $user]);
            if ($request->input('rememberMe'))
            {
                //Tuto Cookie: https://dev.to/webtutor/how-to-get-set-delete-cookie-in-laravel-10-tutorial-55fp
                Cookie::queue('username', $values[0]->username, 10080);
            }
            return redirect('/');
        } else {
            Session()->flash('error', 'Authentication failed: Invalid credentials');
            return redirect('/signin');
        }
    }


}
