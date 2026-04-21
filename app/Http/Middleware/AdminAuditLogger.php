<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Http\Request;

class AdminAuditLogger
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!auth()->check() || !auth()->user()->is_admin) {
            return $response;
        }

        $query = $this->sanitize($request->query());
        $payload = $this->sanitize($request->except(['_token', '_method']));

        AdminAuditLog::create([
            'user_id' => auth()->id(),
            'route_name' => optional($request->route())->getName(),
            'action' => optional($request->route())->getActionName(),
            'method' => strtoupper($request->method()),
            'path' => $request->path(),
            'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'query_params' => $query,
            'payload' => $payload,
        ]);

        return $response;
    }

    private function sanitize(array $data): array
    {
        $sensitive = [
            'password',
            'password_confirmation',
            'token',
            'remember_token',
        ];

        foreach ($sensitive as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = '***';
            }
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && mb_strlen($value) > 1000) {
                $data[$key] = mb_substr($value, 0, 1000) . '...';
            }
        }

        return $data;
    }
}

