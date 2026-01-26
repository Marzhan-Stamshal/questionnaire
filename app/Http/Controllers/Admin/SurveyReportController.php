<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SurveyReportController extends Controller
{
    public function show(Survey $survey)
    {
        $survey->load(['group', 'template']);

        // средние по преподавателям внутри этой анкеты
        $teacherRows = Response::query()
            ->selectRaw('teacher_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->where('survey_id', $survey->id)
            ->whereNotNull('teacher_id')
            ->whereNotNull('value_int')
            ->groupBy('teacher_id')
            ->orderByDesc('avg_score')
            ->get();

        $teacherIds = $teacherRows->pluck('teacher_id')->toArray();
        $teachers = Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');

        $teacherResult = $teacherRows->map(function ($r) use ($teachers) {
            return [
                'teacher' => $teachers[$r->teacher_id] ?? null,
                'avg_score' => round((float)$r->avg_score, 2),
                'answers_count' => (int)$r->answers_count,
            ];
        })->filter(fn($x) => $x['teacher'])->values();

        // средние по вопросам внутри анкеты (по всем преподавателям вместе)
        $questionRows = Response::query()
            ->selectRaw('question_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->where('survey_id', $survey->id)
            ->whereNotNull('value_int')
            ->groupBy('question_id')
            ->orderByDesc('avg_score')
            ->get();

        $qIds = $questionRows->pluck('question_id')->toArray();
        $questions = SurveyQuestion::whereIn('id', $qIds)->get()->keyBy('id');

        $questionResult = $questionRows->map(function ($r) use ($questions) {
            $q = $questions[$r->question_id] ?? null;
            return [
                'question' => $q,
                'avg_score' => round((float)$r->avg_score, 2),
                'answers_count' => (int)$r->answers_count,
            ];
        })->filter(fn($x) => $x['question'])->values();

        return view('admin.reports.surveys.show', compact(
            'survey',
            'teacherResult',
            'questionResult'
        ));
    }

    public function exportRaw(Survey $survey)
    {
        $rows = Response::query()
            ->where('survey_id', $survey->id)
            ->orderBy('id')
            ->get();

        $qIds = $rows->pluck('question_id')->unique()->toArray();
        $tIds = $rows->pluck('teacher_id')->filter()->unique()->toArray();

        $questions = SurveyQuestion::whereIn('id', $qIds)->get()->keyBy('id');
        $teachers = Teacher::whereIn('id', $tIds)->get()->keyBy('id');

        $filename = 'survey_' . $survey->id . '_raw_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows, $questions, $teachers) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                'ID',
                'survey_id',
                'group_id',
                'session_id',
                'teacher_id',
                'teacher_fio',
                'question_id',
                'question_code',
                'question_text',
                'value_int',
                'value_text',
                'created_at'
            ]);

            foreach ($rows as $r) {
                $q = $questions[$r->question_id] ?? null;
                $t = $r->teacher_id ? ($teachers[$r->teacher_id] ?? null) : null;

                fputcsv($out, [
                    $r->id,
                    $r->survey_id,
                    $r->group_id,
                    $r->respondent_session_id,
                    $r->teacher_id,
                    $t ? $t->fio : '',
                    $r->question_id,
                    $q ? $q->code : '',
                    $q ? $q->text : '',
                    $r->value_int,
                    $r->value_text,
                    $r->created_at,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function matrix(\App\Models\Survey $survey)
    {
        $survey->load(['group', 'template']);

        // 1) Вопросы матрицы (0-10, teacher, matrix)
        $matrixQuestions = \App\Models\SurveyQuestion::query()
            ->where('template_id', $survey->template_id)
            ->where('render_mode', 'matrix')
            ->where('target', 'teacher')
            ->where('type', 'scale_0_10')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // 2) Преподаватели этой группы (из teaching_assignments)
        $teachers = \App\Models\TeachingAssignment::where('group_id', $survey->group_id)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->unique('id')
            ->values();

        $questionIds = $matrixQuestions->pluck('id')->toArray();
        $teacherIds = $teachers->pluck('id')->toArray();

        // 3) Считаем средний балл по клеткам: (question_id, teacher_id)
        $cells = \App\Models\Response::query()
            ->selectRaw('question_id, teacher_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->where('survey_id', $survey->id)
            ->whereIn('question_id', $questionIds)
            ->whereIn('teacher_id', $teacherIds)
            ->whereNotNull('value_int')
            ->groupBy('question_id', 'teacher_id')
            ->get();

        // Превратим в удобную матрицу: matrix[question_id][teacher_id] = avg
        $matrix = [];
        foreach ($cells as $c) {
            $matrix[$c->question_id][$c->teacher_id] = [
                'avg' => round((float)$c->avg_score, 2),
                'count' => (int)$c->answers_count,
            ];
        }

        // 4) Средние по преподавателю (итог по столбцу)
        $teacherAvgRows = \App\Models\Response::query()
            ->selectRaw('teacher_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->where('survey_id', $survey->id)
            ->whereIn('question_id', $questionIds)
            ->whereIn('teacher_id', $teacherIds)
            ->whereNotNull('value_int')
            ->groupBy('teacher_id')
            ->get();

        $teacherAverages = [];
        foreach ($teacherAvgRows as $r) {
            $teacherAverages[$r->teacher_id] = [
                'avg' => round((float)$r->avg_score, 2),
                'count' => (int)$r->answers_count,
            ];
        }

        // 5) Средние по вопросу (итог по строке)
        $questionAvgRows = \App\Models\Response::query()
            ->selectRaw('question_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->where('survey_id', $survey->id)
            ->whereIn('question_id', $questionIds)
            ->whereIn('teacher_id', $teacherIds)
            ->whereNotNull('value_int')
            ->groupBy('question_id')
            ->get();

        $questionAverages = [];
        foreach ($questionAvgRows as $r) {
            $questionAverages[$r->question_id] = [
                'avg' => round((float)$r->avg_score, 2),
                'count' => (int)$r->answers_count,
            ];
        }

        return view('admin.reports.surveys.matrix', compact(
            'survey',
            'matrixQuestions',
            'teachers',
            'matrix',
            'teacherAverages',
            'questionAverages'
        ));
    }
    public function teacherInSurvey(\App\Models\Survey $survey, \App\Models\Teacher $teacher)
    {
        $survey->load(['group', 'template']);

        // берём только матричные вопросы шаблона (0-10)
        $matrixQuestions = \App\Models\SurveyQuestion::query()
            ->where('template_id', $survey->template_id)
            ->where('render_mode', 'matrix')
            ->where('target', 'teacher')
            ->where('type', 'scale_0_10')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $qIds = $matrixQuestions->pluck('id')->toArray();

        $overall = \App\Models\Response::query()
            ->where('survey_id', $survey->id)
            ->where('teacher_id', $teacher->id)
            ->whereIn('question_id', $qIds)
            ->whereNotNull('value_int')
            ->avg('value_int');

        $overallCount = \App\Models\Response::query()
            ->where('survey_id', $survey->id)
            ->where('teacher_id', $teacher->id)
            ->whereIn('question_id', $qIds)
            ->whereNotNull('value_int')
            ->count();

        // средний по каждому вопросу
        $perQuestionRows = \App\Models\Response::query()
            ->selectRaw('question_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->where('survey_id', $survey->id)
            ->where('teacher_id', $teacher->id)
            ->whereIn('question_id', $qIds)
            ->whereNotNull('value_int')
            ->groupBy('question_id')
            ->get();

        $map = [];
        foreach ($perQuestionRows as $r) {
            $map[$r->question_id] = [
                'avg' => round((float)$r->avg_score, 2),
                'count' => (int)$r->answers_count,
            ];
        }

        return view('admin.reports.surveys.teacher', [
            'survey' => $survey,
            'teacher' => $teacher,
            'matrixQuestions' => $matrixQuestions,
            'overall' => $overall ? round((float)$overall, 2) : null,
            'overallCount' => $overallCount,
            'map' => $map,
        ]);
    }
    public function comments(\App\Models\Survey $survey)
    {
        $survey->load(['group', 'template']);

        // только text или yes_no_with_text (там value_text)
        $rows = \App\Models\Response::query()
            ->with(['question', 'teacher'])
            ->where('survey_id', $survey->id)
            ->whereNotNull('value_text')
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('admin.reports.surveys.comments', compact('survey', 'rows'));
    }
    public function exportMatrixCsv(\App\Models\Survey $survey)
    {
        $survey->load(['group', 'template']);

        $matrixQuestions = \App\Models\SurveyQuestion::query()
            ->where('template_id', $survey->template_id)
            ->where('render_mode', 'matrix')
            ->where('target', 'teacher')
            ->where('type', 'scale_0_10')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $teachers = \App\Models\TeachingAssignment::where('group_id', $survey->group_id)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->unique('id')
            ->values();

        $qIds = $matrixQuestions->pluck('id')->toArray();
        $tIds = $teachers->pluck('id')->toArray();

        // avg по клеткам
        $cells = \App\Models\Response::query()
            ->selectRaw('question_id, teacher_id, AVG(value_int) as avg_score')
            ->where('survey_id', $survey->id)
            ->whereIn('question_id', $qIds)
            ->whereIn('teacher_id', $tIds)
            ->whereNotNull('value_int')
            ->groupBy('question_id', 'teacher_id')
            ->get();

        $matrix = [];
        foreach ($cells as $c) {
            $matrix[$c->question_id][$c->teacher_id] = round((float)$c->avg_score, 2);
        }

        $filename = "survey_{$survey->id}_matrix_" . date('Y-m-d_H-i') . ".csv";

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($matrixQuestions, $teachers, $matrix) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // header
            $header = ['Question Code', 'Question Text'];
            foreach ($teachers as $t) {
                $header[] = $t->fio;
            }
            fputcsv($out, $header);

            // rows
            foreach ($matrixQuestions as $q) {
                $row = [$q->code, $q->text];

                foreach ($teachers as $t) {
                    $row[] = $matrix[$q->id][$t->id] ?? '';
                }

                fputcsv($out, $row);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
