@extends('layouts.admin')
@section('header', 'Редактирование шаблона')
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Шаблон: {{ $template->title }}</h3>
            <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">Назад</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Редактирование шаблона --}}
        <div class="card mb-3">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.templates.update', $template) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input name="title" class="form-control" value="{{ $template->title }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="2">{{ $template->description }}</textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active"
                            {{ $template->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Активен</label>
                    </div>

                    <button class="btn btn-success">Сохранить</button>
                </form>
            </div>
        </div>

        {{-- Добавить вопрос --}}
        <div class="card mb-3">
            <div class="card-header"><b>Добавить вопрос</b></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.templates.questions.store', $template) }}">
                    @csrf

                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label">Код</label>
                            <input name="code" class="form-control" placeholder="Q1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Текст *</label>
                            <input name="text" class="form-control" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Тип *</label>
                            <select name="type" id="question_type" class="form-select" required>
                                <option value="scale_0_10">Шкала 0–10 (оценка)</option>
                                <option value="yes_no">Да / Нет</option>
                                <option value="text">Текстовый ответ</option>
                                <option value="yes_no_with_text">Да / Нет + комментарий</option>
                                <option value="single_choice">Один вариант из списка</option>
                                <option value="multiple_choice">Несколько вариантов из списка</option>
                            </select>

                        </div>
                        {{-- <div class="col-md-12 mt-2">
                            <label class="form-label">Варианты ответа</label>
                            <textarea name="options_text" class="form-control" rows="4"
                                placeholder="Введите варианты ответов (каждый вариант с новой строки).
Например:
Отлично
Хорошо
Средне
Плохо"></textarea>

                            <div class="form-text">
                                Каждый вариант пишите с новой строки. Эти варианты появятся у студентов как готовые ответы.
                            </div>

                            <div class="form-text">
                                (Необязательно) Можно указать код и текст через символ <b>|</b>, например: <b>A|Отлично</b>
                            </div>
                        </div> --}}


                        <div class="col-md-12 mt-2" id="options_block" style="display:none;">
                            <label class="form-label">Варианты ответа</label>
                            <textarea name="options_text" class="form-control" rows="4"
                                placeholder="Введите варианты ответов (каждый вариант с новой строки).
Например:
Отлично
Хорошо
Средне
Плохо"></textarea>

                            <div class="form-text">
                                Каждый вариант пишите с новой строки. Эти варианты появятся у студентов как готовые ответы.
                            </div>

                            <div class="form-text">
                                (Необязательно) Можно указать код и текст через символ <b>|</b>, например: <b>A|Отлично</b>
                            </div>
                        </div>


                        <div class="col-md-2">
                            <label class="form-label">Сортировка *</label>
                            <input name="sort_order" class="form-control" value="0" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Цель *</label>
                            <select name="target" class="form-select" required>
                                <option value="teacher">Преподаватель (вопрос о конкретном преподавателе)</option>
                                <option value="survey">Анкета / группа (общий вопрос)</option>
                            </select>

                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Режим *</label>
                            <select name="render_mode" class="form-select" required>
                                <option value="single">Обычный вопрос (1 раз на всю анкету)</option>
                                <option value="matrix">Матрица оценок (по каждому преподавателю)</option>
                                <option value="per_teacher">Блок по преподавателю (вопросы для каждого)</option>
                            </select>

                        </div>

                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" name="is_required" checked>
                                <label class="form-check-label">Обязательный</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">Активный</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <button class="btn btn-primary w-100">Добавить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Список вопросов --}}
        <div class="card">
            <div class="card-header"><b>Вопросы ({{ $questions->count() }})</b></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Код</th>
                            <th>Текст</th>
                            <th>Тип</th>
                            <th>Цель</th>
                            <th>Режим</th>
                            <th>Порядок</th>
                            <th>Активен</th>
                            <th width="140">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($questions as $q)
                            <tr>
                                <td><b>{{ $q->code }}</b></td>
                                <td>{{ $q->text }}</td>
                                <td>{{ $q->type }}</td>
                                <td>{{ $q->target }}</td>
                                <td>{{ $q->render_mode }}</td>
                                <td>{{ $q->sort_order }}</td>
                                <td>{{ $q->is_active ? 'Да' : 'Нет' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.questions.destroy', $q) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Удалить вопрос?')"
                                            class="btn btn-sm btn-danger">X</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const typeSelect = document.getElementById("question_type");
            const optionsBlock = document.getElementById("options_block");

            function toggleOptionsBlock() {
                const value = typeSelect.value;

                if (value === "single_choice" || value === "multiple_choice") {
                    optionsBlock.style.display = "block";
                } else {
                    optionsBlock.style.display = "none";
                }
            }

            // при изменении select
            typeSelect.addEventListener("change", toggleOptionsBlock);

            // при загрузке страницы (если уже выбрано)
            toggleOptionsBlock();
        });
    </script>

@endsection
