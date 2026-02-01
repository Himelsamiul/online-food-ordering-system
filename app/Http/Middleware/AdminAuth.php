<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

      
        if (!auth()->user()->is_admin) {
            auth()->logout();
            return redirect()->route('admin.login');
        }

     
        return $next($request);
    }
}
