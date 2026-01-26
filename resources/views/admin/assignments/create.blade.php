@extends('layouts.admin')

@section('content')
    <div class="container">
        <h3 class="mb-3">Добавить связь</h3>

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
                <form method="POST" action="{{ route('admin.assignments.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Группа *</label>
                        <select name="group_id" class="form-select" required>
                            <option value="">-- выбрать группу --</option>
                            @foreach ($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Преподаватель *</label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="">-- выбрать преподавателя --</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->fio }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Год</label>
                        <input name="year" type="number" min="2000" max="2100" class="form-control"
                            placeholder="2025">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Семестр</label>
                        <input name="semester" class="form-control" placeholder="Fall / Spring / 2025-1">
                    </div>

                    <button class="btn btn-success">Сохранить</button>
                    <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
@endsection
