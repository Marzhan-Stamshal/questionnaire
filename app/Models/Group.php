<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
        'faculty',
        'program',
        'course',
        'active'
    ];

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teaching_assignments');
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }
}
