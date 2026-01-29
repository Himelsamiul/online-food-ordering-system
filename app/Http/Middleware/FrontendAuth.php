<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FrontendAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('frontend_user')) {
            return redirect()->route('login')
                ->with('error', 'Please login first');
        }

        return $next($request);
    }
}
