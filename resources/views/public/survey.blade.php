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
                <div class="text-muted">Группа: <b>{{ $survey->group->name }}</b></div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('public.survey.submit', $survey->public_token) }}">
            @csrf

            {{-- Обычные вопросы --}}
            @if ($singleQuestions->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-header"><b>Общие вопросы</b></div>
                    <div class="card-body">
                        @foreach ($singleQuestions as $q)
                            <div class="mb-3">
                                <label class="form-label">
                                    <b>{{ $q->code ?? '' }}</b> {{ $q->text }}
                                </label>

                                @if ($q->type === 'scale_0_10')
                                    <input type="number" min="0" max="10"
                                        name="single[{{ $q->id }}]" class="form-control" placeholder="0-10">
                                @elseif($q->type === 'yes_no')
                                    <select name="single[{{ $q->id }}]" class="form-select">
                                        <option value="">-- выберите --</option>
                                        <option value="1">Да</option>
                                        <option value="0">Нет</option>
                                    </select>
                                @elseif($q->type === 'yes_no')
                                    <select name="single[{{ $q->id }}]" class="form-select">
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

                                    <textarea class="form-control" name="single_text[{{ $q->id }}]" rows="2"
                                        placeholder="Комментарий (если нужно)"></textarea>
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
                                    <textarea name="single[{{ $q->id }}]" class="form-control" rows="3"></textarea>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Матричные вопросы --}}
            @if ($matrixQuestions->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-header"><b>Оценка преподавателей</b></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 320px">Вопрос</th>
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
                                                <div class="text-muted small">Оценка 0–10</div>
                                            </td>

                                            @foreach ($teachers as $t)
                                                <td class="text-center">
                                                    <input type="number" min="0" max="10"
                                                        class="form-control"
                                                        name="matrix[{{ $q->id }}][{{ $t->id }}]"
                                                        placeholder="0-10">
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

                                    {{-- type = text --}}
                                    @if ($q->type === 'text')
                                        <textarea class="form-control" name="per_teacher[{{ $t->id }}][{{ $q->id }}]" rows="3"
                                            {{ $q->is_required ? 'required' : '' }}></textarea>
                                    @endif

                                    {{-- type = yes_no --}}
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
