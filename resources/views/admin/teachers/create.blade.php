@extends('layouts.admin')
@section('header', 'Добавить преподавателя')

@section('content')
    <div class="container">
        <h3 class="mb-3">Добавить преподавателя</h3>

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
                <form method="POST" action="{{ route('admin.teachers.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">ФИО *</label>
                        <input name="fio" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Кафедра</label>
                        <input name="department" class="form-control">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="active" checked>
                        <label class="form-check-label">Активен</label>
                    </div>

                    <button class="btn btn-success">Сохранить</button>
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
@endsection
