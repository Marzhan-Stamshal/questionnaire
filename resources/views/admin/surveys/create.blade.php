@extends('layouts.admin')
@section('header', 'Создать анкету')

@section('content')
    <div class="container">
        <h3 class="mb-3">Создать анкету для группы</h3>

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
                <form method="POST" action="{{ route('admin.surveys.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Шаблон *</label>
                        <select name="template_id" class="form-select" required>
                            <option value="">-- выбрать шаблон --</option>
                            @foreach ($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Группа *</label>
                        <select name="group_id" class="form-select" required>
                            <option value="">-- выбрать группу --</option>
                            @foreach ($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Год</label>
                            <input name="year" type="number" class="form-control" placeholder="2025">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Семестр</label>
                            <input name="semester" class="form-control" placeholder="Fall / Spring / 2025-1">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Статус *</label>
                            <select name="status" class="form-select" required>
                                <option value="draft">draft</option>
                                <option value="active">active</option>
                                <option value="closed">closed</option>
                            </select>
                        </div>
                    </div>

                    {{--   <div class="row g-2 mt-2">
                        <div class="col-md-3">
                            <label class="form-label">Начало</label>
                            <input name="starts_at" type="datetime-local" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Окончание</label>
                            <input name="ends_at" type="datetime-local" class="form-control">
                        </div>
                    </div> --}}
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
                        <button class="btn btn-success">Создать</button>
                        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">Назад</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
