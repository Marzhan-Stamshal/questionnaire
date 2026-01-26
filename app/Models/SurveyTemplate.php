<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyTemplate extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_active'
    ];

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class, 'template_id');
    }
}
