<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    protected $fillable = [
        'template_id',
        'code',
        'text',
        'type',
        'target',
        'render_mode',
        'sort_order',
        'is_required',
        'is_active'
    ];

    public function template()
    {
        return $this->belongsTo(SurveyTemplate::class, 'template_id');
    }
    public function options()
    {
        return $this->hasMany(\App\Models\SurveyQuestionOption::class, 'question_id')->orderBy('sort_order');
    }
}
