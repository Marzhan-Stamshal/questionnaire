@extends('layouts.admin')
@section('header', 'Редактировать анкету')

@section('content')
    <div class="container">
        <h3 class="mb-3">Редактировать анкету</h3>

        <div class="alert alert-info">
            <b>Ссылка для студентов:</b>
            <a target="_blank" href="{{ url('/s/' . $survey->public_token) }}">
                {{ url('/s/' . $survey->public_token) }}
            </a>
        </div>

        <div class="card">
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
                <form method="POST" action="{{ route('admin.surveys.update', $survey) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Шаблон *</label>
                        <select name="template_id" class="form-select" required>
                            @foreach ($templates as $t)
                                <option value="{{ $t->id }}" {{ $survey->template_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Группа *</label>
                        <select name="group_id" class="form-select" required>
                            @foreach ($groups as $g)
                                <option value="{{ $g->id }}" {{ $survey->group_id == $g->id ? 'selected' : '' }}>
                                    {{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Год</label>
                            <input name="year" type="number" class="form-control" value="{{ $survey->year }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Семестр</label>
                            <input name="semester" class="form-control" value="{{ $survey->semester }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Статус *</label>
                            <select name="status" class="form-select" required>
                                <option value="draft" {{ $survey->status === 'draft' ? 'selected' : '' }}>draft</option>
                                <option value="active" {{ $survey->status === 'active' ? 'selected' : '' }}>active</option>
                                <option value="closed" {{ $survey->status === 'closed' ? 'selected' : '' }}>closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Начало (starts_at)</label>
                            <input type="datetime-local" name="starts_at" class="form-control"
                                value="{{ old('starts_at', isset($survey) && $survey->starts_at ? $survey->starts_at->format('Y-m-d\TH:i') : '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Конец (ends_at)</label>
                            <input type="datetime-local" name="ends_at" class="form-control"
                                value="{{ old('ends_at', isset($survey) && $survey->ends_at ? $survey->ends_at->format('Y-m-d\TH:i') : '') }}">
                        </div>
                    </div>


                    <div class="mt-3">
                        <button class="btn btn-success">Обновить</button>
                        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">Назад</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
