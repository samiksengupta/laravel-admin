<?php

namespace Samik\LaravelAdmin\Http\Middlewares;

use Auth;
use Closure;

class AuthenticateAdmin
{
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated as an admin
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        // If not authenticated, redirect to the admin login page
        return redirect()->route('admin.login');
    }
}
