<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            if ($request->expectsJson()) {
                abort(403, 'Unauthorized admin access.');
            }

            return redirect()->route('admin.login')->with('error', 'You must be logged in as an administrator.');
        }

        return $next($request);
    }
}
