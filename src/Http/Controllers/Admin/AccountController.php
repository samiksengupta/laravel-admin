<?php

namespace Samik\LaravelAdmin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Samik\LaravelAdmin\Http\Controllers\AdminBaseController;

class AccountController extends AdminBaseController
{
    public function viewLogin()
    {
        $this->viewData['title'] = 'Login';
        $this->viewData['apiLoginUrl'] = api_admin_url('login');
        return view('laravel-admin::contents.accounts.login', $this->viewData);
    }

    public function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials + ['active' => 1], true)) {

            $request->session()->regenerate();
            return response()->json([
                'message' => 'Your login was Successful. You will be redirected soon.', 
                'navigate' => session()->get('url.intended', \admin_url('/')), 
                'timeout' => 2000,
                'authenticated' => Auth::check()
            ], 200);
        }
        
        return response()->json(['message' => 'Login failed. Invalid credentials or user deactivated.'], 401);
    }

    public function apiLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Your have been logged out. You will be redirected soon.', 'navigate' => admin_url('login')], 200);
    }
}
