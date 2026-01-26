<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $group = Group::create([
            'name' => 'TEST-21-01',
            'faculty' => 'Тестовый факультет',
            'program' => 'Тестовая ОП',
            'course' => 4,
            'active' => 1,
        ]);

        $t1 = Teacher::create(['fio' => 'Иванов И.И.', 'department' => 'Кафедра 1', 'active' => 1]);
        $t2 = Teacher::create(['fio' => 'Петров П.П.', 'department' => 'Кафедра 2', 'active' => 1]);

        TeachingAssignment::create(['group_id' => $group->id, 'teacher_id' => $t1->id, 'year' => 2025, 'semester' => 'Fall']);
        TeachingAssignment::create(['group_id' => $group->id, 'teacher_id' => $t2->id, 'year' => 2025, 'semester' => 'Fall']);

        $template = SurveyTemplate::create([
            'title' => 'Преподаватель глазами студентов',
            'description' => 'Анонимная анкета',
            'is_active' => 1,
        ]);

        // обычный вопрос
        SurveyQuestion::create([
            'template_id' => $template->id,
            'code' => 'G1',
            'text' => 'Общее впечатление от семестра (комментарий)',
            'type' => 'text',
            'target' => 'survey',
            'render_mode' => 'single',
            'sort_order' => 1,
        ]);

        // матричные вопросы (пример 2)
        SurveyQuestion::create([
            'template_id' => $template->id,
            'code' => 'Q1',
            'text' => 'Содержание дисциплины соответствует силлабусу',
            'type' => 'scale_0_10',
            'target' => 'teacher',
            'render_mode' => 'matrix',
            'sort_order' => 10,
        ]);

        SurveyQuestion::create([
            'template_id' => $template->id,
            'code' => 'Q2',
            'text' => 'Преподаватель объясняет материал понятно',
            'type' => 'scale_0_10',
            'target' => 'teacher',
            'render_mode' => 'matrix',
            'sort_order' => 20,
        ]);

        $survey = Survey::create([
            'template_id' => $template->id,
            'group_id' => $group->id,
            'year' => 2025,
            'semester' => 'Fall',
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'public_token' => Str::random(40),
        ]);

        echo "Survey link: /s/{$survey->public_token}\n";
    }
}
