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

        // If it's an API route, return JSON response
        if ($request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Otherwise redirect
        return redirect()->route('admin.login');
    }
}
