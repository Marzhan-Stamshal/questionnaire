<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $fillable = [
        'respondent_session_id',
        'survey_id',
        'group_id',
        'question_id',
        'teacher_id',
        'value_int',
        'value_text'
    ];

    public function session()
    {
        return $this->belongsTo(RespondentSession::class, 'respondent_session_id');
    }

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'question_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    public function survey()
    {
        return $this->belongsTo(\App\Models\Survey::class, 'survey_id');
    }

    public function respondentSession()
    {
        return $this->belongsTo(\App\Models\RespondentSession::class, 'respondent_session_id');
    }
}
