@extends('layouts.admin')
@section('header', 'Импорт данных')

@section('content')
    <div class="container">
        <h3 class="mb-3">📥 Импорт данных (CSV)</h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="alert alert-info">
            Формат файлов CSV должен быть в UTF-8.<br>
            Excel: <b>Сохранить как → CSV UTF-8</b>
        </div>

        <div class="row g-3">

            {{-- Groups --}}
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header"><b>Импорт групп</b></div>
                    <div class="card-body">
                        <div class="small text-muted mb-2">
                            Колонки: <code>name,faculty,program,course,active</code>
                        </div>

                        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.import.groups') }}">
                            @csrf
                            <input type="file" name="file" class="form-control mb-2" required>
                            <button class="btn btn-primary w-100">Загрузить</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Teachers --}}
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header"><b>Импорт преподавателей</b></div>
                    <div class="card-body">
                        <div class="small text-muted mb-2">
                            Колонки: <code>fio,department,active</code>
                        </div>

                        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.import.teachers') }}">
                            @csrf
                            <input type="file" name="file" class="form-control mb-2" required>
                            <button class="btn btn-primary w-100">Загрузить</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Assignments --}}
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header"><b>Импорт связей</b></div>
                    <div class="card-body">
                        <div class="small text-muted mb-2">
                            Колонки: <code>group_name,teacher_fio,year,semester</code>
                        </div>

                        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.import.assignments') }}">
                            @csrf
                            <input type="file" name="file" class="form-control mb-2" required>
                            <button class="btn btn-primary w-100">Загрузить</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
