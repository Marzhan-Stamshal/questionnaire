<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $survey->template->title }} — {{ $survey->group->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-4">

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h4 class="mb-1">{{ $survey->template->title }}</h4>
                <div class="text-muted">Цикл: <b>{{ $survey->group->name }}</b></div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('public.survey.submit', $survey->public_token) }}">
            @csrf

            {{-- 1) Обычные вопросы ДО первой матрицы --}}
            @if ($singleBefore->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-header"></div>
                    <div class="card-body">
                        @foreach ($singleBefore as $q)
                            <div class="mb-3">
                                <label class="form-label">
                                    <b>{{ $q->code ?? '' }}</b> {{ $q->text }}
                                    @if ($q->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if ($q->type === 'scale_0_10')
                                    <input type="number" min="0" max="100"
                                        name="single[{{ $q->id }}]" class="form-control" placeholder="0-100"
                                        {{ $q->is_required ? 'required' : '' }}>
                                @elseif($q->type === 'yes_no')
                                    <select name="single[{{ $q->id }}]" class="form-select"
                                        {{ $q->is_required ? 'required' : '' }}>
                                        <option value="">-- выберите --</option>
                                        <option value="1">Иә/Да</option>
                                        <option value="0">Жоқ/Нет</option>
                                    </select>
                                @elseif ($q->type === 'yes_no_with_text')
                                    <div class="d-flex gap-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="single_yesno[{{ $q->id }}]" value="1"
                                                {{ $q->is_required ? 'required' : '' }}>
                                            <label class="form-check-label">Иә/Да</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="single_yesno[{{ $q->id }}]" value="0">
                                            <label class="form-check-label">Жоқ/Нет</label>
                                        </div>
                                    </div>

                                    <textarea class="form-control" name="single_text[{{ $q->id }}]" rows="2" placeholder=""></textarea>
                                @elseif ($q->type === 'single_choice')
                                    @foreach ($q->options as $opt)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="single_choice[{{ $q->id }}]"
                                                value="{{ $opt->value ?? $opt->label }}"
                                                {{ $q->is_required ? 'required' : '' }}>
                                            <label class="form-check-label">{{ $opt->label }}</label>
                                        </div>
                                    @endforeach
                                @elseif ($q->type === 'multiple_choice')
                                    @foreach ($q->options as $opt)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="single_multi[{{ $q->id }}][]"
                                                value="{{ $opt->value ?? $opt->label }}">
                                            <label class="form-check-label">{{ $opt->label }}</label>
                                        </div>
                                    @endforeach
                                @else
                                    <textarea name="single[{{ $q->id }}]" class="form-control" rows="3"
                                        {{ $q->is_required ? 'required' : '' }}></textarea>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 2) ОДНА общая матрица со ВСЕМИ матричными вопросами --}}
            @if ($matrixQuestions->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <b>0-ден 100-ге дейін бағалаңыз / Оцените по шкале от 1 до 100.</b><br>
                        * 0–49 – мүлдем қанағаттанбаймын / совсем неудовлетворён(а),<br>
                        50–59 – өте төмен / очень низкая,<br>
                        60–69 – төмен / низкая,<br>
                        70–79 – орташа / средняя,<br>
                        80–89 – жеткілікті / умеренная,<br>
                        90–99 – жоғары / высокая,<br>
                        100 – толық қанағаттандым / полностью удовлетворён(а).<br>
                        Сұраққа жауап беру үшін, оны тиісті түрде бағалаңыз!/Для ответа на вопрос проставьте
                        соответствующую оценку!
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 320px">Сұрақ/Вопрос</th>
                                        @foreach ($teachers as $t)
                                            <th class="text-center" style="min-width: 180px">{{ $t->fio }}</th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($matrixQuestions as $q)
                                        <tr>
                                            <td>
                                                <b>{{ $q->code ?? '' }}</b> {{ $q->text }}


                                            </td>

                                            @foreach ($teachers as $t)
                                                <td class="text-center">

                                                    {{-- YES/NO --}}
                                                    @if ($q->type === 'yes_no')
                                                        <select class="form-select"
                                                            name="matrix[{{ $q->id }}][{{ $t->id }}]"
                                                            {{ $q->is_required ? 'required' : '' }}>
                                                            <option value="">--</option>
                                                            <option value="1">Иә/Да</option>
                                                            <option value="0">Жоқ/Нет</option>
                                                        </select>

                                                        {{-- YES/NO + TEXT --}}
                                                    @elseif ($q->type === 'yes_no_with_text')
                                                        <select class="form-select mb-2"
                                                            name="matrix_yesno[{{ $q->id }}][{{ $t->id }}]"
                                                            {{ $q->is_required ? 'required' : '' }}>
                                                            <option value="">--</option>
                                                            <option value="1">Иә/Да</option>
                                                            <option value="0">Жоқ/Нет</option>
                                                        </select>

                                                        <textarea class="form-control" name="matrix_text[{{ $q->id }}][{{ $t->id }}]" rows="2"
                                                            placeholder="Комментарий (если нужно)"></textarea>

                                                        {{-- SCALE (0-100) --}}
                                                    @else
                                                        <input type="number" min="0" max="100"
                                                            class="form-control"
                                                            name="matrix[{{ $q->id }}][{{ $t->id }}]"
                                                            placeholder="0-100"
                                                            {{ $q->is_required ? 'required' : '' }}>
                                                    @endif

                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 3) Обычные вопросы ПОСЛЕ матрицы --}}
            @if ($singleAfter->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-header"></div>
                    <div class="card-body">
                        @foreach ($singleAfter as $q)
                            <div class="mb-3">
                                <label class="form-label">
                                    <b>{{ $q->code ?? '' }}</b> {{ $q->text }}
                                    @if ($q->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if ($q->type === 'scale_0_10')
                                    <input type="number" min="0" max="10"
                                        name="single[{{ $q->id }}]" class="form-control" placeholder="0-10"
                                        {{ $q->is_required ? 'required' : '' }}>
                                @elseif($q->type === 'yes_no')
                                    <select name="single[{{ $q->id }}]" class="form-select"
                                        {{ $q->is_required ? 'required' : '' }}>
                                        <option value="">-- выберите --</option>
                                        <option value="1">Да</option>
                                        <option value="0">Нет</option>
                                    </select>
                                @elseif ($q->type === 'yes_no_with_text')
                                    <div class="d-flex gap-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="single_yesno[{{ $q->id }}]" value="1"
                                                {{ $q->is_required ? 'required' : '' }}>
                                            <label class="form-check-label">Да</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="single_yesno[{{ $q->id }}]" value="0">
                                            <label class="form-check-label">Нет</label>
                                        </div>
                                    </div>
                                @elseif ($q->type === 'single_choice')
                                    @foreach ($q->options as $opt)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="single_choice[{{ $q->id }}]"
                                                value="{{ $opt->value ?? $opt->label }}"
                                                {{ $q->is_required ? 'required' : '' }}>
                                            <label class="form-check-label">{{ $opt->label }}</label>
                                        </div>
                                    @endforeach
                                @elseif ($q->type === 'multiple_choice')
                                    @foreach ($q->options as $opt)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="single_multi[{{ $q->id }}][]"
                                                value="{{ $opt->value ?? $opt->label }}">
                                            <label class="form-check-label">{{ $opt->label }}</label>
                                        </div>
                                    @endforeach
                                @else
                                    <textarea name="single[{{ $q->id }}]" class="form-control" rows="3"
                                        {{ $q->is_required ? 'required' : '' }}></textarea>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 4) per_teacher как было (если нужно — можно тоже “вклинивать” по sort_order, но ты не просил) --}}
            @if ($perTeacherQuestions->count())
                <hr class="my-4">
                <h5 class="mb-3">Вопросы по каждому преподавателю</h5>

                @foreach ($teachers as $t)
                    <div class="card mb-3">
                        <div class="card-header">
                            <b>{{ $t->fio }}</b>
                        </div>
                        <div class="card-body">

                            @foreach ($perTeacherQuestions as $q)
                                <div class="mb-3">
                                    <label class="form-label">
                                        <b>{{ $q->code }}</b> — {{ $q->text }}
                                        @if ($q->is_required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    @if ($q->type === 'text')
                                        <textarea class="form-control" name="per_teacher[{{ $t->id }}][{{ $q->id }}]" rows="3"
                                            {{ $q->is_required ? 'required' : '' }}></textarea>
                                    @endif

                                    @if ($q->type === 'yes_no')
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="per_teacher[{{ $t->id }}][{{ $q->id }}]"
                                                    value="1" {{ $q->is_required ? 'required' : '' }}>
                                                <label class="form-check-label">Да</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="per_teacher[{{ $t->id }}][{{ $q->id }}]"
                                                    value="0">
                                                <label class="form-check-label">Нет</label>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($q->type === 'yes_no_with_text')
                                        <div class="d-flex gap-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="per_teacher_yesno[{{ $t->id }}][{{ $q->id }}]"
                                                    value="1" {{ $q->is_required ? 'required' : '' }}>
                                                <label class="form-check-label">Да</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="per_teacher_yesno[{{ $t->id }}][{{ $q->id }}]"
                                                    value="0">
                                                <label class="form-check-label">Нет</label>
                                            </div>
                                        </div>

                                        <textarea class="form-control" name="per_teacher_text[{{ $t->id }}][{{ $q->id }}]" rows="2"
                                            placeholder="Комментарий (если нужно)"></textarea>
                                    @endif

                                    @if ($q->type === 'single_choice')
                                        @foreach ($q->options as $opt)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="per_teacher_choice[{{ $t->id }}][{{ $q->id }}]"
                                                    value="{{ $opt->value ?? $opt->label }}"
                                                    {{ $q->is_required ? 'required' : '' }}>
                                                <label class="form-check-label">{{ $opt->label }}</label>
                                            </div>
                                        @endforeach
                                    @endif

                                    @if ($q->type === 'multiple_choice')
                                        @foreach ($q->options as $opt)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    name="per_teacher_multi[{{ $t->id }}][{{ $q->id }}][]"
                                                    value="{{ $opt->value ?? $opt->label }}">
                                                <label class="form-check-label">{{ $opt->label }}</label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach

                        </div>
                    </div>
                @endforeach
            @endif

            <div class="d-grid">
                <button class="btn btn-primary btn-lg">Отправить анкету</button>
            </div>

        </form>
    </div>
</body>

</html>
