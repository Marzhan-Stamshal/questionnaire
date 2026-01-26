<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\SurveyQuestion;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherReportController extends Controller
{
    // общий рейтинг преподавателей
    /* public function index(Request $request)
    {
        $templateId = $request->get('template_id'); // опционально фильтр по шаблону
        $year = $request->get('year');              // опционально
        $semester = $request->get('semester');      // опционально

        // берём только матричные оценочные вопросы (0-10)
        $questionIdsQuery = SurveyQuestion::query()
            ->where('type', 'scale_0_10')
            ->where('target', 'teacher')
            ->where('render_mode', 'matrix')
            ->where('is_active', 1);

        if ($templateId) {
            $questionIdsQuery->where('template_id', $templateId);
        }

        $questionIds = $questionIdsQuery->pluck('id')->toArray();

        // основной запрос: средний балл по каждому преподавателю
        $rows = Response::query()
            ->selectRaw('teacher_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->whereNotNull('teacher_id')
            ->whereIn('question_id', $questionIds)
            ->groupBy('teacher_id')
            ->orderByDesc('avg_score')
            ->get();

        $teacherIds = $rows->pluck('teacher_id')->toArray();
        $teachers = Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');

        // формируем массив под view
        $result = $rows->map(function ($r) use ($teachers) {
            return [
                'teacher' => $teachers[$r->teacher_id] ?? null,
                'avg_score' => round((float)$r->avg_score, 2),
                'answers_count' => (int)$r->answers_count,
            ];
        })->filter(fn($x) => $x['teacher'] !== null)->values();

        return view('admin.reports.teachers.index', compact('result'));
    } */
    public function index(\Illuminate\Http\Request $request)
    {
        $templateId = $request->get('template_id');
        $year = $request->get('year');
        $semester = $request->get('semester');
        $groupId = $request->get('group_id');
        $minN = (int)($request->get('min_n', 0));

        // вопросы для расчёта (оценочные матричные)
        $questionIdsQuery = \App\Models\SurveyQuestion::query()
            ->where('type', 'scale_0_10')
            ->where('target', 'teacher')
            ->whereIn('render_mode', ['matrix', 'per_teacher']) // если оценки будут и per_teacher
            ->where('is_active', 1);

        if ($templateId) {
            $questionIdsQuery->where('template_id', $templateId);
        }

        $questionIds = $questionIdsQuery->pluck('id')->toArray();

        // базовый query ответов
        $resp = \App\Models\Response::query()
            ->whereNotNull('teacher_id')
            ->whereIn('question_id', $questionIds)
            ->whereNotNull('value_int');

        // фильтры по анкете через join surveys
        $resp->join('surveys', 'responses.survey_id', '=', 'surveys.id');

        if ($year) {
            $resp->where('surveys.year', (int)$year);
        }
        if ($semester) {
            $resp->where('surveys.semester', $semester);
        }
        if ($groupId) {
            $resp->where('surveys.group_id', (int)$groupId);
        }
        if ($templateId) {
            $resp->where('surveys.template_id', (int)$templateId);
        }

        // агрегат по преподавателю
        $rows = $resp->selectRaw('responses.teacher_id, AVG(responses.value_int) as avg_score, COUNT(*) as answers_count')
            ->groupBy('responses.teacher_id')
            ->orderByDesc('avg_score')
            ->get();

        if ($minN > 0) {
            $rows = $rows->filter(fn($r) => (int)$r->answers_count >= $minN)->values();
        }

        $teacherIds = $rows->pluck('teacher_id')->toArray();
        $teachers = \App\Models\Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');

        $result = $rows->map(function ($r) use ($teachers) {
            return [
                'teacher' => $teachers[$r->teacher_id] ?? null,
                'avg_score' => round((float)$r->avg_score, 2),
                'answers_count' => (int)$r->answers_count,
            ];
        })->filter(fn($x) => $x['teacher'] !== null)->values();

        // данные для фильтров в UI
        $templates = \App\Models\SurveyTemplate::orderBy('title')->get();
        $groups = \App\Models\Group::orderBy('name')->get();

        return view('admin.reports.teachers.index', compact(
            'result',
            'templates',
            'groups',
            'templateId',
            'year',
            'semester',
            'groupId',
            'minN'
        ));
    }

    // детальный отчёт по одному преподавателю
    public function show(Teacher $teacher)
    {
        // средний по всем матричным вопросам 0-10
        $overall = Response::query()
            ->where('teacher_id', $teacher->id)
            ->whereNotNull('value_int')
            ->avg('value_int');

        $overallCount = Response::query()
            ->where('teacher_id', $teacher->id)
            ->whereNotNull('value_int')
            ->count();

        // средний по каждому вопросу
        $perQuestion = Response::query()
            ->selectRaw('question_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->where('teacher_id', $teacher->id)
            ->whereNotNull('value_int')
            ->groupBy('question_id')
            ->orderByDesc('avg_score')
            ->get();

        $qIds = $perQuestion->pluck('question_id')->toArray();
        $questions = SurveyQuestion::whereIn('id', $qIds)->get()->keyBy('id');

        $perQuestionResult = $perQuestion->map(function ($r) use ($questions) {
            $q = $questions[$r->question_id] ?? null;

            return [
                'question' => $q,
                'avg_score' => round((float)$r->avg_score, 2),
                'answers_count' => (int)$r->answers_count,
            ];
        })->filter(fn($x) => $x['question'] !== null)->values();

        return view('admin.reports.teachers.show', [
            'teacher' => $teacher,
            'overall' => $overall ? round((float)$overall, 2) : null,
            'overallCount' => $overallCount,
            'perQuestionResult' => $perQuestionResult
        ]);
    }

    // public function exportTeachersCsv()
    // {
    //     $rows = \App\Models\Response::query()
    //         ->selectRaw('teacher_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
    //         ->whereNotNull('teacher_id')
    //         ->whereNotNull('value_int')
    //         ->groupBy('teacher_id')
    //         ->orderByDesc('avg_score')
    //         ->get();

    //     $teacherIds = $rows->pluck('teacher_id')->toArray();
    //     $teachers = \App\Models\Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');

    //     $filename = 'teachers_rating_' . date('Y-m-d_H-i') . '.csv';

    //     $headers = [
    //         "Content-Type" => "text/csv; charset=UTF-8",
    //         "Content-Disposition" => "attachment; filename=\"$filename\"",
    //     ];

    //     $callback = function () use ($rows, $teachers) {
    //         $out = fopen('php://output', 'w');

    //         // чтобы Excel правильно видел UTF-8
    //         fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

    //         fputcsv($out, ['Teacher ID', 'ФИО', 'Средний балл', 'Кол-во ответов']);

    //         foreach ($rows as $r) {
    //             $t = $teachers[$r->teacher_id] ?? null;
    //             if (!$t) continue;

    //             fputcsv($out, [
    //                 $t->id,
    //                 $t->fio,
    //                 round((float)$r->avg_score, 2),
    //                 (int)$r->answers_count,
    //             ]);
    //         }

    //         fclose($out);
    //     };

    //     return response()->stream($callback, 200, $headers);
    // }
    public function exportTeachersCsv(\Illuminate\Http\Request $request)
    {
        $templateId = $request->get('template_id');
        $year = $request->get('year');
        $semester = $request->get('semester');
        $groupId = $request->get('group_id');
        $minN = (int)($request->get('min_n', 0));

        $questionIdsQuery = \App\Models\SurveyQuestion::query()
            ->where('type', 'scale_0_10')
            ->where('target', 'teacher')
            ->whereIn('render_mode', ['matrix', 'per_teacher'])
            ->where('is_active', 1);

        if ($templateId) {
            $questionIdsQuery->where('template_id', $templateId);
        }

        $questionIds = $questionIdsQuery->pluck('id')->toArray();

        $resp = \App\Models\Response::query()
            ->whereNotNull('responses.teacher_id')
            ->whereIn('responses.question_id', $questionIds)
            ->whereNotNull('responses.value_int')
            ->join('surveys', 'responses.survey_id', '=', 'surveys.id');

        if ($year) $resp->where('surveys.year', (int)$year);
        if ($semester) $resp->where('surveys.semester', $semester);
        if ($groupId) $resp->where('surveys.group_id', (int)$groupId);
        if ($templateId) $resp->where('surveys.template_id', (int)$templateId);

        $rows = $resp->selectRaw('responses.teacher_id, AVG(responses.value_int) as avg_score, COUNT(*) as answers_count')
            ->groupBy('responses.teacher_id')
            ->orderByDesc('avg_score')
            ->get();

        if ($minN > 0) {
            $rows = $rows->filter(fn($r) => (int)$r->answers_count >= $minN)->values();
        }

        $teacherIds = $rows->pluck('teacher_id')->toArray();
        $teachers = \App\Models\Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');

        $filename = 'teachers_rating_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows, $teachers) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['Teacher ID', 'ФИО', 'Средний балл', 'Кол-во ответов']);

            foreach ($rows as $r) {
                $t = $teachers[$r->teacher_id] ?? null;
                if (!$t) continue;

                fputcsv($out, [
                    $t->id,
                    $t->fio,
                    round((float)$r->avg_score, 2),
                    (int)$r->answers_count,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function exportTeacherDetailCsv(\App\Models\Teacher $teacher)
    {
        $perQuestion = \App\Models\Response::query()
            ->selectRaw('question_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->where('teacher_id', $teacher->id)
            ->whereNotNull('value_int')
            ->groupBy('question_id')
            ->orderByDesc('avg_score')
            ->get();

        $qIds = $perQuestion->pluck('question_id')->toArray();
        $questions = \App\Models\SurveyQuestion::whereIn('id', $qIds)->get()->keyBy('id');

        $filename = 'teacher_' . $teacher->id . '_detail_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($teacher, $perQuestion, $questions) {
            $out = fopen('php://output', 'w');

            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['Преподаватель', $teacher->fio]);
            fputcsv($out, []);
            fputcsv($out, ['Question ID', 'Код', 'Вопрос', 'Средний балл', 'Кол-во ответов']);

            foreach ($perQuestion as $r) {
                $q = $questions[$r->question_id] ?? null;
                if (!$q) continue;

                fputcsv($out, [
                    $q->id,
                    $q->code,
                    $q->text,
                    round((float)$r->avg_score, 2),
                    (int)$r->answers_count,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
