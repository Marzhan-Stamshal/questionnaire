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
                            <select name="type" class="form-select" required>
                                <option value="scale_0_10">scale_0_10</option>
                                <option value="yes_no">yes_no</option>
                                <option value="text">text</option>
                                <option value="yes_no_with_text">yes_no_with_text</option>
                                <option value="single_choice">single_choice</option>
                                <option value="multiple_choice">multiple_choice</option>

                            </select>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label class="form-label">Варианты (для single_choice / multiple_choice)</label>
                            <textarea name="options_text" class="form-control" rows="4"
                                placeholder="Каждая строка — один вариант.&#10;Например:&#10;Очень доволен&#10;Нормально&#10;Плохо"></textarea>
                            <div class="form-text">Если нужно “value|label”: пишите так: <b>A|Очень доволен</b></div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Сортировка *</label>
                            <input name="sort_order" class="form-control" value="0" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Цель *</label>
                            <select name="target" class="form-select" required>
                                <option value="teacher">teacher</option>
                                <option value="survey">survey</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Режим *</label>
                            <select name="render_mode" class="form-select" required>
                                <option value="matrix">matrix</option>
                                <option value="single">single</option>
                                <option value="per_teacher">per_teacher</option>
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
@endsection
