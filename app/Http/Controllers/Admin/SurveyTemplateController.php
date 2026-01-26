<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use Illuminate\Http\Request;

class SurveyTemplateController extends Controller
{
    public function index()
    {
        $templates = SurveyTemplate::orderBy('id', 'desc')->paginate(20);
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $request->has('is_active');

        $template = SurveyTemplate::create($data);

        return redirect()->route('admin.templates.edit', $template)->with('success', 'Шаблон создан ✅');
    }

    public function edit(SurveyTemplate $template)
    {
        $questions = SurveyQuestion::where('template_id', $template->id)
            ->orderBy('sort_order')
            ->get();

        return view('admin.templates.edit', compact('template', 'questions'));
    }

    public function update(Request $request, SurveyTemplate $template)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable',
        ]);
        dd($request);

        $data['is_active'] = $request->has('is_active');

        $template->update($data);

        return redirect()->route('admin.templates.edit', $template)->with('success', 'Шаблон обновлён ✅');
    }

    public function destroy(SurveyTemplate $template)
    {
        $template->delete();
        return redirect()->route('admin.templates.index')->with('success', 'Шаблон удалён ✅');
    }
}
