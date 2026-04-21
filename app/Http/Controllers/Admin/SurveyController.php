<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::with(['group', 'template'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.surveys.index', compact('surveys'));
    }

    public function create()
    {
        $groups = Group::orderBy('name')->get();
        $templates = SurveyTemplate::orderBy('title')->get();

        return view('admin.surveys.create', compact('groups', 'templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:survey_templates,id',
            'group_id' => 'required|exists:groups,id',
            'year' => 'nullable|integer|min:2000|max:2100',
            'semester' => 'nullable|string|max:50',
            'status' => ['required', 'string', Rule::in(Survey::allowedStatuses())],
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['public_token'] = Str::random(40);

        $survey = Survey::create($data);

        return redirect()->route('admin.surveys.index')
            ->with('success', 'Анкета создана ✅ Ссылка готова!');
    }

    public function edit(Survey $survey)
    {
        $groups = Group::orderBy('name')->get();
        $templates = SurveyTemplate::orderBy('title')->get();

        return view('admin.surveys.edit', compact('survey', 'groups', 'templates'));
    }

    public function update(Request $request, Survey $survey)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:survey_templates,id',
            'group_id' => 'required|exists:groups,id',
            'year' => 'nullable|integer|min:2000|max:2100',
            'semester' => 'nullable|string|max:50',
            'status' => ['required', 'string', Rule::in(Survey::allowedStatuses())],
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $survey->update($data);

        return redirect()->route('admin.surveys.index')->with('success', 'Анкета обновлена ✅');
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();
        return redirect()->route('admin.surveys.index')->with('success', 'Анкета удалена ✅');
    }
}
