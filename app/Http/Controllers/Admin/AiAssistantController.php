<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Services\AiAgentService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function index(Request $request, AiAgentService $ai)
    {
        $surveys = Survey::with(['group', 'template'])->orderByDesc('id')->limit(200)->get();
        $surveyId = $request->filled('survey_id') ? (int) $request->input('survey_id') : null;
        $health = $ai->health();

        return view('admin.ai.index', [
            'surveys' => $surveys,
            'surveyId' => $surveyId,
            'aiResult' => null,
            'aiError' => null,
            'health' => $health,
        ]);
    }

    public function summarize(Request $request, AiAgentService $ai)
    {
        $data = $request->validate([
            'survey_id' => 'required|exists:surveys,id',
        ]);

        $survey = Survey::with(['group', 'template'])->findOrFail((int) $data['survey_id']);
        $surveys = Survey::with(['group', 'template'])->orderByDesc('id')->limit(200)->get();

        $riskQuestions = SurveyQuestion::query()
            ->where('template_id', $survey->template_id)
            ->where('is_active', 1)
            ->whereIn('type', ['yes_no', 'yes_no_with_text'])
            ->where(function ($q) {
                $q->where('text', 'like', '%вымог%')
                    ->orWhere('text', 'like', '%взят%')
                    ->orWhere('text', 'like', '%домог%')
                    ->orWhere('text', 'like', '%корруп%')
                    ->orWhere('text', 'like', '%неофициаль%')
                    ->orWhere('text', 'like', '%пара%');
            })
            ->orderBy('sort_order')
            ->get();

        $qIds = $riskQuestions->pluck('id')->toArray();
        $responses = Response::with(['question', 'teacher'])
            ->where('survey_id', $survey->id)
            ->whereIn('question_id', $qIds)
            ->orderByDesc('id')
            ->get();

        $summary = $responses->groupBy('question_id')->map(function ($items, $questionId) use ($riskQuestions) {
            $question = $riskQuestions->firstWhere('id', (int) $questionId);
            $yes = $items->where('value_int', 1)->count();
            $no = $items->where('value_int', 0)->count();
            $total = $items->count();

            $topTeachers = $items
                ->where('value_int', 1)
                ->groupBy('teacher_id')
                ->map(function ($group) {
                    return [
                        'teacher' => optional($group->first()->teacher)->fio,
                        'yes_count' => $group->count(),
                    ];
                })
                ->sortByDesc('yes_count')
                ->take(5)
                ->values();

            $comments = $items
                ->filter(fn($r) => is_string($r->value_text) && trim($r->value_text) !== '')
                ->pluck('value_text')
                ->take(20)
                ->values();

            return [
                'question_code' => $question->code ?? null,
                'question_text' => $question->text ?? null,
                'yes_count' => $yes,
                'no_count' => $no,
                'total' => $total,
                'yes_share_percent' => $total > 0 ? round(($yes / $total) * 100, 1) : 0,
                'top_teachers_by_yes' => $topTeachers,
                'comments_sample' => $comments,
            ];
        })->values();

        $context = [
            'survey' => [
                'id' => $survey->id,
                'group' => $survey->group->name ?? null,
                'template' => $survey->template->title ?? null,
                'year' => $survey->year,
                'semester' => $survey->semester,
            ],
            'risk_questions_count' => $riskQuestions->count(),
            'risk_summary' => $summary,
        ];

        $aiResult = null;
        $aiError = null;
        $health = $ai->health();

        try {
            if (!$health['ok']) {
                throw new \RuntimeException('Нет подключения к Ollama');
            }
            $aiResult = $ai->summarizeSurveyRisks($context);
        } catch (\Throwable $e) {
            $aiError = 'Не удалось получить ответ от AI. Проверьте Ollama и модель. Текст ошибки: ' . $e->getMessage();
        }

        return view('admin.ai.index', [
            'surveys' => $surveys,
            'surveyId' => $survey->id,
            'aiResult' => $aiResult,
            'aiError' => $aiError,
            'health' => $health,
        ]);
    }
}
