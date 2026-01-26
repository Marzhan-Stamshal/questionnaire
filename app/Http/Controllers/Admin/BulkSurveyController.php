<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BulkSurveyController extends Controller
{
    public function create()
    {
        $templates = SurveyTemplate::orderBy('title')->get();
        return view('admin.surveys.bulk-create', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:survey_templates,id',
            'year' => 'nullable|integer|min:2000|max:2100',
            'semester' => 'nullable|string|max:50',
            'status' => 'required|in:draft,active,closed',
            'only_active_groups' => 'nullable',
        ]);

        $groupsQuery = Group::query()->orderBy('name');
        if ($request->has('only_active_groups')) {
            $groupsQuery->where('active', 1);
        }
        $groups = $groupsQuery->get();

        $created = [];
        $skipped = 0;

        foreach ($groups as $g) {
            // защита от дублей: если уже есть анкета для этой группы с таким шаблоном+год+семестр
            $exists = Survey::where('group_id', $g->id)
                ->where('template_id', $data['template_id'])
                ->where('year', $data['year'] ?? null)
                ->where('semester', $data['semester'] ?? null)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $survey = Survey::create([
                'template_id' => $data['template_id'],
                'group_id' => $g->id,
                'year' => $data['year'] ?? null,
                'semester' => $data['semester'] ?? null,
                'status' => $data['status'],
                'public_token' => Str::random(40),
            ]);

            $created[] = $survey;
        }

        // подгружаем группы, чтобы показать таблицу ссылок
        $surveyIds = collect($created)->pluck('id')->toArray();
        $createdSurveys = Survey::with(['group', 'template'])
            ->whereIn('id', $surveyIds)
            ->orderBy('group_id')
            ->get();

        return view('admin.surveys.bulk-result', [
            'createdSurveys' => $createdSurveys,
            'skipped' => $skipped,
        ]);
    }
}
