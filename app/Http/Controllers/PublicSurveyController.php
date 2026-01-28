<?php

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\RespondentSession;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Group;


class PublicSurveyController extends Controller
{
    public function show($token)
    {
        $survey = Survey::with(['template', 'group'])
            ->where('public_token', $token)
            ->firstOrFail();

        if ($survey->status !== 'active') {
            abort(403, 'Анкета не активна.');
        }

        $now = now();

        if ($survey->starts_at && $now->lt($survey->starts_at)) {
            return view('public.survey-not-started', compact('survey'));
        }

        if ($survey->ends_at && $now->gt($survey->ends_at)) {
            return view('public.survey-ended', compact('survey'));
        }

        $sessionCookieKey = 'survey_session_' . $survey->id;
        $sessionId = request()->cookie($sessionCookieKey);
        if (!$sessionId) {
            $sessionId = (string) \Illuminate\Support\Str::uuid();
        }

        $submittedCookieKey = 'survey_submitted_' . $survey->id;
        if (request()->cookie($submittedCookieKey)) {
            return view('public.already-submitted', compact('survey'));
        }

        $teachers = TeachingAssignment::where('group_id', $survey->group_id)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->unique('id')
            ->values();

        $questions = SurveyQuestion::with('options')
            ->where('template_id', $survey->template_id)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // 1) per_teacher оставим отдельно (как у тебя было)
        $perTeacherQuestions = $questions->where('render_mode', 'per_teacher')->values();

        // 2) single + matrix — делаем “до первой матрицы / матрица / после матрицы”
        $singleBefore = collect();
        $singleAfter  = collect();
        $matrixQuestions = collect();

        $seenFirstMatrix = false;

        foreach ($questions as $q) {
            if ($q->render_mode === 'per_teacher') {
                continue;
            }

            if ($q->render_mode === 'matrix') {
                $seenFirstMatrix = true;
                $matrixQuestions->push($q);
                continue;
            }

            // всё что НЕ matrix и НЕ per_teacher считаем “обычными”
            if (!$seenFirstMatrix) {
                $singleBefore->push($q);
            } else {
                $singleAfter->push($q);
            }
        }
        $matrixScale = $matrixQuestions->whereIn('type', ['scale_0_10'])->values();
        $matrixYesNo = $matrixQuestions->whereIn('type', ['yes_no'])->values();

        // если у тебя шкала 0-100 хранится тоже как scale_0_10 — ок,
        // иначе добавь свой тип сюда (например 'scale_0_100')

        $response = response()->view('public.survey', compact(
            'survey',
            'teachers',
            'singleBefore',
            'matrixQuestions',
            'matrixScale',
            'matrixYesNo',
            'singleAfter',
            'perTeacherQuestions',
            'sessionId'
        ));

        return $response->cookie($sessionCookieKey, $sessionId, 60 * 24 * 30);
    }



    public function submit(Request $request, $token)
    {
        $survey = Survey::where('public_token', $token)->firstOrFail();
        $sessionCookieKey = 'survey_session_' . $survey->id;
        $sessionId = $request->cookie($sessionCookieKey);

        if (!$sessionId) {
            $sessionId = (string) Str::uuid();
        }

        if ($survey->status !== 'active') {
            abort(403, 'Анкета не активна.');
        }

        $now = now();

        if ($survey->starts_at && $now->lt($survey->starts_at)) {
            abort(403, 'Анкета ещё не началась.');
        }

        if ($survey->ends_at && $now->gt($survey->ends_at)) {
            abort(403, 'Анкетирование завершено.');
        }

        $questions = SurveyQuestion::where('template_id', $survey->template_id)
            ->where('is_active', 1)
            ->get();

        // создаём анонимную сессию
        $sessionToken = Str::random(40);
        $tokenHash = hash('sha256', $sessionToken);

        DB::transaction(function () use ($survey, $questions, $request, $tokenHash) {

            $session = RespondentSession::create([
                'survey_id' => $survey->id,
                'group_id' => $survey->group_id,
                'token_hash' => $tokenHash,
                'submitted_at' => now(),
            ]);

            /**
             * 1) SINGLE (без teacher_id)
             */
            foreach ($questions->where('render_mode', 'single') as $q) {

                $valueInt = null;
                $valueText = null;

                if ($q->type === 'scale_0_10' || $q->type === 'yes_no') {
                    $valueInt = $request->input("single.{$q->id}");
                } elseif ($q->type === 'text') {
                    $valueText = $request->input("single.{$q->id}");
                } elseif ($q->type === 'yes_no_with_text') {
                    $valueInt = $request->input("single_yesno.{$q->id}");
                    $valueText = $request->input("single_text.{$q->id}");
                } elseif ($q->type === 'single_choice') {
                    $valueText = $request->input("single_choice.{$q->id}");
                } elseif ($q->type === 'multiple_choice') {
                    $arr = $request->input("single_multi.{$q->id}", []);
                    if (!empty($arr)) {
                        $valueText = json_encode(array_values($arr), JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    $valueText = $request->input("single.{$q->id}");
                }

                if (($valueInt === null || $valueInt === '') && ($valueText === null || $valueText === '')) {
                    continue;
                }

                Response::create([
                    'respondent_session_id' => $session->id,
                    'survey_id' => $survey->id,
                    'group_id' => $survey->group_id,
                    'question_id' => $q->id,
                    'teacher_id' => null,
                    'value_int' => ($valueInt === '' ? null : (is_numeric($valueInt) ? (int) $valueInt : null)),
                    'value_text' => ($valueText === '' ? null : (string) $valueText),
                ]);
            }

            /**
             * 2) MATRIX (teacher_id обязателен)
             *
             * Поддержка:
             * - scale (0..100) -> matrix[qid][teacher]
             * - yes_no (1/0)   -> matrix[qid][teacher]
             * - yes_no_with_text:
             *      matrix_yesno[qid][teacher]
             *      matrix_text[qid][teacher]
             */
            $matrixYesNo = $request->input('matrix_yesno', []); // [qid][teacher] => 1/0
            $matrixText  = $request->input('matrix_text', []);  // [qid][teacher] => comment

            foreach ($questions->where('render_mode', 'matrix') as $q) {

                // 2.1) matrix yes_no_with_text
                if ($q->type === 'yes_no_with_text') {
                    $ynMap = $matrixYesNo[$q->id] ?? [];
                    $txMap = $matrixText[$q->id] ?? [];

                    foreach ($ynMap as $teacherId => $yn) {
                        if ($yn === null || $yn === '') continue;

                        $comment = $txMap[$teacherId] ?? null;

                        Response::create([
                            'respondent_session_id' => $session->id,
                            'survey_id' => $survey->id,
                            'group_id' => $survey->group_id,
                            'question_id' => $q->id,
                            'teacher_id' => (int) $teacherId,
                            'value_int' => (int) $yn,
                            'value_text' => ($comment === '' ? null : $comment),
                        ]);
                    }

                    continue; // важно: не падать дальше в обычную matrix-обработку
                }

                // 2.2) matrix scale / yes_no (оба приходят из matrix[qid][teacher])
                $teachersAnswers = $request->input("matrix.{$q->id}", []);

                foreach ($teachersAnswers as $teacherId => $val) {
                    if ($val === null || $val === '') continue;

                    Response::create([
                        'respondent_session_id' => $session->id,
                        'survey_id' => $survey->id,
                        'group_id' => $survey->group_id,
                        'question_id' => $q->id,
                        'teacher_id' => (int) $teacherId,
                        'value_int' => is_numeric($val) ? (int) $val : null,
                        'value_text' => null,
                    ]);
                }
            }

            /**
             * 3) per_teacher[text or yes_no] -> per_teacher[teacher_id][question_id]
             */
            $perTeacher = $request->input('per_teacher', []);
            foreach ($perTeacher as $teacherId => $qMap) {
                foreach ($qMap as $questionId => $value) {
                    if ($value === null || $value === '') continue;

                    Response::create([
                        'survey_id' => $survey->id,
                        'group_id' => $survey->group_id,
                        'question_id' => (int) $questionId,
                        'teacher_id' => (int) $teacherId,
                        'respondent_session_id' => $session->id,
                        'value_int' => is_numeric($value) ? (int) $value : null,
                        'value_text' => !is_numeric($value) ? (string) $value : null,
                    ]);
                }
            }

            /**
             * 4) per_teacher yes/no + text
             */
            $perTeacherYesNo = $request->input('per_teacher_yesno', []);
            $perTeacherText  = $request->input('per_teacher_text', []);

            foreach ($perTeacherYesNo as $teacherId => $qMap) {
                foreach ($qMap as $questionId => $yn) {
                    $comment = $perTeacherText[$teacherId][$questionId] ?? null;

                    if ($yn === null || $yn === '') continue;

                    Response::create([
                        'survey_id' => $survey->id,
                        'group_id' => $survey->group_id,
                        'question_id' => (int) $questionId,
                        'teacher_id' => (int) $teacherId,
                        'respondent_session_id' => $session->id,
                        'value_int' => (int) $yn,
                        'value_text' => ($comment === '' ? null : $comment),
                    ]);
                }
            }

            /**
             * 5) per_teacher single_choice
             */
            $perTeacherChoice = $request->input('per_teacher_choice', []);
            foreach ($perTeacherChoice as $teacherId => $qMap) {
                foreach ($qMap as $questionId => $val) {
                    if ($val === null || $val === '') continue;

                    Response::create([
                        'survey_id' => $survey->id,
                        'group_id' => $survey->group_id,
                        'question_id' => (int) $questionId,
                        'teacher_id' => (int) $teacherId,
                        'respondent_session_id' => $session->id,
                        'value_int' => null,
                        'value_text' => (string) $val,
                    ]);
                }
            }

            /**
             * 6) per_teacher multiple_choice
             */
            $perTeacherMulti = $request->input('per_teacher_multi', []);
            foreach ($perTeacherMulti as $teacherId => $qMap) {
                foreach ($qMap as $questionId => $arr) {
                    if (!is_array($arr) || empty($arr)) continue;

                    Response::create([
                        'survey_id' => $survey->id,
                        'group_id' => $survey->group_id,
                        'question_id' => (int) $questionId,
                        'teacher_id' => (int) $teacherId,
                        'respondent_session_id' => $session->id,
                        'value_int' => null,
                        'value_text' => json_encode(array_values($arr), JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        });

        $submittedKey = 'survey_submitted_' . $survey->id;

        return redirect()->route('public.survey.show', $survey->public_token)
            ->with('success', 'Спасибо! Анкета отправлена ✅')
            ->cookie($submittedKey, '1', 60 * 24 * 365);
    }



    // public function chooseGroup()
    // {
    //     $groups = Group::where('active', 1)->orderBy('name')->get();
    //     return view('public.choose-group', compact('groups'));
    // }

    // public function goToSurvey(Request $request)
    // {
    //     $data = $request->validate([
    //         'group_id' => 'required|exists:groups,id',
    //     ]);

    //     $groupId = (int)$data['group_id'];

    //     // Берём последнюю активную анкету для группы
    //     $survey = \App\Models\Survey::where('group_id', $groupId)
    //         ->where('status', 'active')
    //         ->orderByDesc('id')
    //         ->first();

    //     if (!$survey) {
    //         return back()->with('error', 'Для выбранной группы нет активной анкеты. Обратитесь к администратору.');
    //     }

    //     return redirect()->route('public.survey.show', $survey->public_token);
    // }
    public function chooseGroup()
    {
        $groups = Group::where('active', 1)->orderBy('name')->get();
        return view('public.choose-group', compact('groups'));
    }

    public function goToSurvey(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $groupId = (int)$data['group_id'];

        $survey = Survey::where('group_id', $groupId)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        if (!$survey) {
            return back()->with('error', 'Для выбранной группы нет активной анкеты. Обратитесь к администратору.');
        }

        return redirect()->route('public.survey.show', $survey->public_token);
    }
}
