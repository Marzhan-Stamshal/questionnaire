<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use Illuminate\Http\Request;

class SurveyQuestionController extends Controller
{
    public function store(Request $request, SurveyTemplate $template)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50',
            'text' => 'required|string',
            'type' => 'required|string|max:30',
            'target' => 'required|string|max:20',
            'render_mode' => 'required|string|max:20',
            'sort_order' => 'required|integer|min:0|max:9999',
            'is_required' => 'nullable',
            'is_active' => 'nullable',
            'options_text' => 'nullable|string',

        ]);

        $data['template_id'] = $template->id;
        $data['is_required'] = $request->has('is_required');
        $data['is_active'] = $request->has('is_active');

        $question = SurveyQuestion::create($data);
        if (in_array($question->type, ['single_choice', 'multiple_choice'])) {
            $raw = $request->input('options_text', '');
            $lines = preg_split("/\r\n|\n|\r/", trim($raw));

            // очищаем старые (на случай если это update)
            \App\Models\SurveyQuestionOption::where('question_id', $question->id)->delete();

            $sort = 0;
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;

                $value = null;
                $label = $line;

                // поддержка "value|label"
                if (strpos($line, '|') !== false) {
                    [$value, $label] = array_map('trim', explode('|', $line, 2));
                }

                \App\Models\SurveyQuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'value' => $value,
                    'sort_order' => $sort++,
                ]);
            }
        }


        return redirect()->route('admin.templates.edit', $template)->with('success', 'Вопрос добавлен ✅');
    }

    public function update(Request $request, SurveyQuestion $question)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50',
            'text' => 'required|string',
            'type' => 'required|string|max:30',
            'target' => 'required|string|max:20',
            'render_mode' => 'required|string|max:20',
            'sort_order' => 'required|integer|min:0|max:9999',
            'is_required' => 'nullable',
            'is_active' => 'nullable',
            'options_text' => 'nullable|string',
        ]);

        $data['is_required'] = $request->has('is_required');
        $data['is_active'] = $request->has('is_active');

        $question->update($data);
        if (in_array($question->type, ['single_choice', 'multiple_choice'])) {
            $raw = $request->input('options_text', '');
            $lines = preg_split("/\r\n|\n|\r/", trim($raw));

            // очищаем старые (на случай если это update)
            \App\Models\SurveyQuestionOption::where('question_id', $question->id)->delete();

            $sort = 0;
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;

                $value = null;
                $label = $line;

                // поддержка "value|label"
                if (strpos($line, '|') !== false) {
                    [$value, $label] = array_map('trim', explode('|', $line, 2));
                }

                \App\Models\SurveyQuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'value' => $value,
                    'sort_order' => $sort++,
                ]);
            }
        }

        return back()->with('success', 'Вопрос обновлён ✅');
    }

    public function destroy(SurveyQuestion $question)
    {
        $templateId = $question->template_id;
        $question->delete();

        return redirect()->route('admin.templates.edit', $templateId)->with('success', 'Вопрос удалён ✅');
    }
}
