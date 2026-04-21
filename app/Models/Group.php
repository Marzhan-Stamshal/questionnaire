<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public const KIND_CYCLE = 'cycle';
    public const KIND_GROUP = 'group';

    protected $fillable = [
        'name',
        'kind',
        'faculty',
        'program',
        'course',
        'active'
    ];

    public function getKindLabelAttribute()
    {
        return $this->kind === self::KIND_GROUP ? 'Группа' : 'Цикл';
    }

    public static function allowedKinds(): array
    {
        return [self::KIND_CYCLE, self::KIND_GROUP];
    }

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
