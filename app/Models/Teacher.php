<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Group;


class Teacher extends Model
{
    protected $fillable = [
        'fio',
        'department',
        'active'
    ];

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'teaching_assignments');
    }
}
