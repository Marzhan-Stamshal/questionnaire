<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (!auth()->user()->is_admin) {
            abort(403, 'Доступ запрещён');
        }

        return $next($request);
    }
}
