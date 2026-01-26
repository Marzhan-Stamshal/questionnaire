<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Response;
use App\Models\Survey;
use App\Models\Teacher;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $activeSurveys = Survey::where('status', 'active')->count();
        $totalSurveys = Survey::count();
        $groupsCount = Group::count();
        $teachersCount = Teacher::count();

        // уникальные "сессии" ответов по всем анкетам
        // если respondent_session_id у вас integer (RespondentSession.id) — считаем distinct
        $uniqueSessions = Response::distinct('respondent_session_id')->count('respondent_session_id');

        // ответы за сегодня
        $todayResponses = Response::whereDate('created_at', today())->count();

        // топ 5 преподавателей по среднему (по всем данным)
        $topTeachersRows = Response::query()
            ->selectRaw('teacher_id, AVG(value_int) as avg_score, COUNT(*) as answers_count')
            ->whereNotNull('teacher_id')
            ->whereNotNull('value_int')
            ->groupBy('teacher_id')
            ->havingRaw('COUNT(*) >= 5')
            ->orderByDesc('avg_score')
            ->limit(5)
            ->get();

        $teacherIds = $topTeachersRows->pluck('teacher_id')->toArray();
        $teachers = Teacher::whereIn('id', $teacherIds)->get()->keyBy('id');

        $topTeachers = $topTeachersRows->map(function ($r) use ($teachers) {
            $t = $teachers[$r->teacher_id] ?? null;
            return [
                'teacher' => $t,
                'avg_score' => round((float)$r->avg_score, 2),
                'answers_count' => (int)$r->answers_count,
            ];
        })->filter(fn($x) => $x['teacher'])->values();

        // группы без анкет
        $groupsWithoutSurvey = Group::whereDoesntHave('surveys')->count();

        return view('admin.dashboard', compact(
            'activeSurveys',
            'totalSurveys',
            'groupsCount',
            'teachersCount',
            'uniqueSessions',
            'todayResponses',
            'topTeachers',
            'groupsWithoutSurvey'
        ));
    }
}
