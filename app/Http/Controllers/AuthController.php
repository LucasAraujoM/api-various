<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'Login successful');
        }
        return redirect()->back()->with('error', 'Invalid credentials')->withInput();
    }

    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'password_confirmation' => ['required', 'same:password'],
            'name' => ['required', 'max:30']
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }
        $user = User::create([
            'email' => $request->email,
            'password' => $request->password,
            'name' => $request->name
        ]);
        Auth::login($user);
        return redirect()->route('dashboard')->with('success', 'Register successful');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('home')->with('success', 'Logout successful');
    }
}
