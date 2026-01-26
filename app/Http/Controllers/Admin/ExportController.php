<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Response;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function responsesCsv(Request $request)
    {
        // фильтры (можно не передавать)
        $surveyId = $request->get('survey_id');
        $groupId  = $request->get('group_id');
        $year     = $request->get('year');
        $semester = $request->get('semester');

        $q = Response::query()
            ->with(['question', 'teacher', 'survey.group', 'survey.template', 'respondentSession'])
            ->orderBy('survey_id')
            ->orderBy('respondent_session_id')
            ->orderBy('question_id');

        if ($surveyId) $q->where('survey_id', (int)$surveyId);
        if ($groupId)  $q->where('group_id', (int)$groupId);

        if ($year || $semester) {
            $q->whereHas('survey', function ($sq) use ($year, $semester) {
                if ($year) $sq->where('year', (int)$year);
                if ($semester) $sq->where('semester', $semester);
            });
        }

        $filename = 'responses_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($q) {
            $out = fopen('php://output', 'w');

            // BOM для Excel (иначе кириллица может сломаться)
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // заголовки CSV
            fputcsv($out, [
                'Survey ID',
                'Template',
                'Group',
                'Year',
                'Semester',
                'RespondentSession ID',
                'Submitted At',
                'Question ID',
                'Question Code',
                'Question Text',
                'Render Mode',
                'Type',
                'Teacher ID',
                'Teacher FIO',
                'Value (int)',
                'Value (text)',
                'Created At',
            ]);

            // пишем по частям, чтобы не съесть память
            $q->chunk(2000, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    $survey = $r->survey;

                    $templateTitle = $survey && $survey->template ? $survey->template->title : '';
                    $groupName = $survey && $survey->group ? $survey->group->name : '';

                    $qType = $r->question ? $r->question->type : '';
                    $valueText = $r->value_text ?? '';

                    // multiple_choice хранится JSON -> делаем "A; B; C"
                    if ($qType === 'multiple_choice' && $valueText) {
                        $arr = json_decode($valueText, true);
                        if (is_array($arr)) {
                            $valueText = implode('; ', $arr);
                        }
                    }

                    fputcsv($out, [
                        $r->survey_id,
                        $templateTitle,
                        $groupName,
                        $survey ? $survey->year : '',
                        $survey ? $survey->semester : '',
                        $r->respondent_session_id,
                        $r->respondentSession ? $r->respondentSession->submitted_at : '',
                        $r->question_id,
                        $r->question ? $r->question->code : '',
                        $r->question ? $r->question->text : '',
                        $r->question ? $r->question->render_mode : '',
                        $qType,
                        $r->teacher_id,
                        $r->teacher ? $r->teacher->fio : '',
                        $r->value_int,
                        $valueText,
                        $r->created_at,
                    ]);
                }
            });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
