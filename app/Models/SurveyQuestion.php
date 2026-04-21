<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    public const TYPE_SCALE_0_10 = 'scale_0_10';
    public const TYPE_YES_NO = 'yes_no';
    public const TYPE_TEXT = 'text';
    public const TYPE_YES_NO_WITH_TEXT = 'yes_no_with_text';
    public const TYPE_SINGLE_CHOICE = 'single_choice';
    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    public const TARGET_SURVEY = 'survey';
    public const TARGET_TEACHER = 'teacher';

    public const RENDER_SINGLE = 'single';
    public const RENDER_MATRIX = 'matrix';
    public const RENDER_PER_TEACHER = 'per_teacher';

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

    public static function allowedTypes(): array
    {
        return [
            self::TYPE_SCALE_0_10,
            self::TYPE_YES_NO,
            self::TYPE_TEXT,
            self::TYPE_YES_NO_WITH_TEXT,
            self::TYPE_SINGLE_CHOICE,
            self::TYPE_MULTIPLE_CHOICE,
        ];
    }

    public static function allowedTargets(): array
    {
        return [self::TARGET_SURVEY, self::TARGET_TEACHER];
    }

    public static function allowedRenderModes(): array
    {
        return [self::RENDER_SINGLE, self::RENDER_MATRIX, self::RENDER_PER_TEACHER];
    }

    public function template()
    {
        return $this->belongsTo(SurveyTemplate::class, 'template_id');
    }
    public function options()
    {
        return $this->hasMany(\App\Models\SurveyQuestionOption::class, 'question_id')->orderBy('sort_order');
    }
}
