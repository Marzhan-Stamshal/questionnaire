<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'route_name',
        'action',
        'method',
        'path',
        'status_code',
        'ip_address',
        'user_agent',
        'query_params',
        'payload',
    ];

    protected $casts = [
        'query_params' => 'array',
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

