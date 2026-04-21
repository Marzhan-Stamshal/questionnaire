<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SensitiveReportAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Доступ запрещён');
        }

        if (!auth()->user()->can_view_sensitive_reports) {
            abort(403, 'Недостаточно прав для просмотра чувствительных отчётов');
        }

        return $next($request);
    }
}

