<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Group;

class TeachingAssignment extends Model
{
    protected $fillable = [
        'group_id',
        'teacher_id',
        'semester',
        'year'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
