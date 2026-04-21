@extends('layouts.admin')
@section('header', 'Редактировать группу')

@section('content')
    <div class="container">
        <h3 class="mb-3">Редактировать группу</h3>

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
                <form method="POST" action="{{ route('admin.groups.update', $group) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input name="name" class="form-control" value="{{ $group->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Тип *</label>
                        <select name="kind" class="form-select" required>
                            <option value="cycle" {{ old('kind', $group->kind) === 'cycle' ? 'selected' : '' }}>Цикл</option>
                            <option value="group" {{ old('kind', $group->kind) === 'group' ? 'selected' : '' }}>Группа</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Факультет</label>
                        <input name="faculty" class="form-control" value="{{ $group->faculty }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ОП</label>
                        <input name="program" class="form-control" value="{{ $group->program }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Курс</label>
                        <input name="course" type="number" class="form-control" min="1" max="7"
                            value="{{ $group->course }}">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="active"
                            {{ $group->active ? 'checked' : '' }}>
                        <label class="form-check-label">Активна</label>
                    </div>

                    <button class="btn btn-success">Обновить</button>
                    <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
@endsection
