<?php

//Authentification now comes from here:
//Uses Laravel more properly
//https://laravel.com/learn/getting-started-with-laravel/basic-authentication-registration

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'username' => 'required|string|max:20|unique:users',
            'email' => 'string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        // Create the user
        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        // Log them in
        Auth::login($user);

        // Redirect to home
        return redirect('/');
    }
}
