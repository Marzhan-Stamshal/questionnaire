<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Group;

class Survey extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

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

    public static function allowedStatuses(): array
    {
        return [self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_CLOSED];
    }
}
