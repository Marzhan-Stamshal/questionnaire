<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RespondentSession;
use App\Models\Response;
use App\Models\Survey;

class SurveySessionsController extends Controller
{
    public function index(Survey $survey)
    {
        $sessions = RespondentSession::where('survey_id', $survey->id)
            ->orderByDesc('id')
            ->paginate(50);

        // количество ответов на каждую сессию (быстро)
        $counts = Response::selectRaw('respondent_session_id, COUNT(*) as cnt')
            ->where('survey_id', $survey->id)
            ->groupBy('respondent_session_id')
            ->pluck('cnt', 'respondent_session_id');

        return view('admin.reports.surveys.sessions', compact('survey', 'sessions', 'counts'));
    }

    public function show(Survey $survey, RespondentSession $session)
    {
        abort_if($session->survey_id !== $survey->id, 404);

        $responses = Response::with(['question', 'teacher'])
            ->where('survey_id', $survey->id)
            ->where('respondent_session_id', $session->id)
            ->orderBy('question_id')
            ->get();

        return view('admin.reports.surveys.session-show', compact('survey', 'session', 'responses'));
    }
}
