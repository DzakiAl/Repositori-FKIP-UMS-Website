<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function ShowLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard'); // Redirect if already logged in
        }

        return view('dashboard.login');
    }

    public function Login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
    
        // Find the user by username
        $user = User::where('name', $request->username)->first();
    
        // Check if user exists and password matches
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            return redirect()->route('dashboard')->with('success', "Successfully Log In");
        } else {
            return back()->withErrors(['message' => 'Invalid username or password']);
        }
    }

    public function Logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('show_login_form');
    }
}