<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespondentSession extends Model
{
    protected $fillable = [
        'survey_id',
        'group_id',
        'token_hash',
        'submitted_at'
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
