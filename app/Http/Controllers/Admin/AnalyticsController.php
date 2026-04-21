<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Response;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\Teacher;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $surveyId = $request->filled('survey_id') ? (int) $request->input('survey_id') : null;
        $groupId = $request->filled('group_id') ? (int) $request->input('group_id') : null;
        $templateId = $request->filled('template_id') ? (int) $request->input('template_id') : null;
        $teacherId = $request->filled('teacher_id') ? (int) $request->input('teacher_id') : null;
        $questionId = $request->filled('question_id') ? (int) $request->input('question_id') : null;
        $questionType = $request->input('question_type');
        $year = $request->filled('year') ? (int) $request->input('year') : null;
        $semester = $request->input('semester');
        $answerMode = $request->input('answer_mode');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $search = trim((string) $request->input('search'));

        $templates = SurveyTemplate::orderBy('title')->get();
        $groups = Group::orderBy('name')->get();
        $teachers = Teacher::where('active', 1)->orderBy('fio')->get();
        $surveys = Survey::with(['group', 'template'])->orderByDesc('id')->limit(500)->get();

        $questionListQuery = SurveyQuestion::query()->where('is_active', 1);
        if ($templateId) {
            $questionListQuery->where('template_id', $templateId);
        }
        $questions = $questionListQuery->orderBy('sort_order')->get();

        $query = Response::query()
            ->with(['survey.group', 'survey.template', 'question', 'teacher', 'respondentSession']);

        if ($surveyId) {
            $query->where('survey_id', $surveyId);
        }
        if ($groupId) {
            $query->where('group_id', $groupId);
        }
        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }
        if ($questionId) {
            $query->where('question_id', $questionId);
        }
        if ($dateFrom) {
            $query->whereDate('responses.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('responses.created_at', '<=', $dateTo);
        }

        if ($templateId || $year || $semester) {
            $query->whereHas('survey', function ($sq) use ($templateId, $year, $semester) {
                if ($templateId) {
                    $sq->where('template_id', $templateId);
                }
                if ($year) {
                    $sq->where('year', $year);
                }
                if ($semester) {
                    $sq->where('semester', $semester);
                }
            });
        }

        if ($questionType) {
            $query->whereHas('question', fn($qq) => $qq->where('type', $questionType));
        }

        if ($answerMode === 'yes') {
            $query->where('value_int', 1);
        } elseif ($answerMode === 'no') {
            $query->where('value_int', 0);
        } elseif ($answerMode === 'with_text') {
            $query->whereNotNull('value_text')->where('value_text', '<>', '');
        } elseif ($answerMode === 'with_int') {
            $query->whereNotNull('value_int');
        }

        if ($search !== '') {
            $query->where(function ($w) use ($search) {
                $w->where('value_text', 'like', '%' . $search . '%')
                    ->orWhereHas('question', function ($qq) use ($search) {
                        $qq->where('text', 'like', '%' . $search . '%')
                            ->orWhere('code', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('teacher', fn($tq) => $tq->where('fio', 'like', '%' . $search . '%'));
            });
        }

        $statsBase = clone $query;

        $totalResponses = (int) (clone $statsBase)->count();
        $uniqueSessions = (int) (clone $statsBase)->whereNotNull('respondent_session_id')->distinct('respondent_session_id')->count('respondent_session_id');
        $yesCount = (int) (clone $statsBase)->where('value_int', 1)->count();
        $noCount = (int) (clone $statsBase)->where('value_int', 0)->count();
        $avgScore = (clone $statsBase)->whereNotNull('value_int')->avg('value_int');

        $topQuestionRows = (clone $statsBase)
            ->selectRaw('question_id, COUNT(*) as cnt')
            ->groupBy('question_id')
            ->orderByDesc('cnt')
            ->limit(5)
            ->get();

        $topQuestionIds = $topQuestionRows->pluck('question_id')->filter()->toArray();
        $topQuestions = SurveyQuestion::whereIn('id', $topQuestionIds)->get()->keyBy('id');
        $topQuestionStats = $topQuestionRows->map(function ($r) use ($topQuestions) {
            $q = $topQuestions[$r->question_id] ?? null;
            return [
                'label' => $q ? (($q->code ? $q->code . ' | ' : '') . $q->text) : ('Вопрос #' . $r->question_id),
                'count' => (int) $r->cnt,
            ];
        });

        $rows = $query->orderByDesc('id')->paginate(100)->withQueryString();

        return view('admin.reports.analytics.index', compact(
            'rows',
            'templates',
            'groups',
            'teachers',
            'surveys',
            'questions',
            'surveyId',
            'groupId',
            'templateId',
            'teacherId',
            'questionId',
            'questionType',
            'year',
            'semester',
            'answerMode',
            'dateFrom',
            'dateTo',
            'search',
            'totalResponses',
            'uniqueSessions',
            'yesCount',
            'noCount',
            'avgScore',
            'topQuestionStats'
        ));
    }
}

