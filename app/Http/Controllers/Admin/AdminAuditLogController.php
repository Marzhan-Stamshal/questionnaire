<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $routeName = trim((string) $request->input('route_name'));
        $method = trim((string) $request->input('method'));
        $status = $request->filled('status') ? (int) $request->input('status') : null;
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $search = trim((string) $request->input('search'));

        $users = User::where('is_admin', 1)->orderBy('name')->get();

        $query = AdminAuditLog::query()
            ->with('user')
            ->orderByDesc('id');

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($routeName !== '') {
            $query->where('route_name', 'like', '%' . $routeName . '%');
        }
        if ($method !== '') {
            $query->where('method', strtoupper($method));
        }
        if ($status) {
            $query->where('status_code', $status);
        }
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('path', 'like', '%' . $search . '%')
                    ->orWhere('route_name', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $logs = $query->paginate(100)->withQueryString();

        return view('admin.audit.index', compact(
            'logs',
            'users',
            'userId',
            'routeName',
            'method',
            'status',
            'dateFrom',
            'dateTo',
            'search'
        ));
    }
}

