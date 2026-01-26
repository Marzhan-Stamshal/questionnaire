@extends('layouts.admin')
@section('header', 'Создать шаблон')

@section('content')
    <div class="container">
        <h3 class="mb-3">Создать шаблон</h3>

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

                <form method="POST" action="{{ route('admin.templates.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" checked>
                        <label class="form-check-label">Активен</label>
                    </div>

                    <button class="btn btn-success">Создать</button>
                    <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
@endsection
