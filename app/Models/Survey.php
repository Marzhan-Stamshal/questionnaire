<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Group;

class Survey extends Model
{
    protected $fillable = [
        'template_id',
        'group_id',
        'semester',
        'year',
        'status',
        'starts_at',
        'ends_at',
        'public_token'
    ];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(SurveyTemplate::class, 'template_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function respondentSessions()
    {
        return $this->hasMany(RespondentSession::class);
    }
}
