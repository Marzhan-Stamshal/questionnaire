@extends('layouts.admin')
@section('header', 'Массовое создание анкет')

@section('content')
    <div class="container" style="max-width: 900px;">
        <h3 class="mb-3">⚡ Массовое создание анкет</h3>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.surveys.bulk.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Шаблон *</label>
                        <select name="template_id" class="form-select" required>
                            <option value="">-- выбрать --</option>
                            @foreach ($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->title }}</option>
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

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="only_active_groups" checked>
                        <label class="form-check-label">Только активные группы</label>
                    </div>

                    <button class="btn btn-primary mt-3 w-100">
                        Создать анкеты всем группам
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
