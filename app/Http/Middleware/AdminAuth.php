<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        // 1️⃣ user logged in না থাকলে
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        // 2️⃣ user admin না হলে
        if (!auth()->user()->is_admin) {
            auth()->logout();
            return redirect()->route('admin.login');
        }

        // 3️⃣ সব ঠিক থাকলে route allow
        return $next($request);
    }
}
