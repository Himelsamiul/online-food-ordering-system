<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendAuth
{
    public function handle(Request $request, Closure $next)
    {
      
        if (! Auth::guard('frontend')->check()) {
            return redirect()->route('login')
                ->with('error', 'Please login first');
        }

        return $next($request);
    }
}
