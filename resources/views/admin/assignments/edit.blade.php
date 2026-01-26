@extends('layouts.admin')

@section('content')
    <div class="container">
        <h3 class="mb-3">Редактировать связь</h3>

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

                <form method="POST" action="{{ route('admin.assignments.update', $assignment) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Группа *</label>
                        <select name="group_id" class="form-select" required>
                            @foreach ($groups as $g)
                                <option value="{{ $g->id }}" {{ $assignment->group_id == $g->id ? 'selected' : '' }}>
                                    {{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Преподаватель *</label>
                        <select name="teacher_id" class="form-select" required>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}"
                                    {{ $assignment->teacher_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->fio }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Год</label>
                        <input name="year" type="number" min="2000" max="2100" class="form-control"
                            value="{{ $assignment->year }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Семестр</label>
                        <input name="semester" class="form-control" value="{{ $assignment->semester }}">
                    </div>

                    <button class="btn btn-success">Обновить</button>
                    <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
@endsection
