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

        <div class="card soft-card mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-2">Шаблоны CSV</div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.import.template', 'groups') }}">Скачать шаблон: Группы</a>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.import.template', 'teachers') }}">Скачать шаблон: Преподаватели</a>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.import.template', 'assignments') }}">Скачать шаблон: Связи</a>
                </div>
                <div class="small text-muted mt-2">Для проверки файла перед загрузкой включите “Сухой запуск”.</div>
            </div>
        </div>

        @if (session('import_report'))
            @php $report = session('import_report'); @endphp
            <div class="card soft-card mb-3">
                <div class="card-header">
                    <b>Отчёт импорта</b> {{ $report['dry_run'] ? '(сухой запуск)' : '' }}
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-2"><span class="chip">Строк: {{ $report['stats']['total_rows'] ?? 0 }}</span></div>
                        <div class="col-md-2"><span class="chip">Создано: {{ $report['stats']['created'] ?? 0 }}</span></div>
                        <div class="col-md-2"><span class="chip">Обновлено: {{ $report['stats']['updated'] ?? 0 }}</span></div>
                        <div class="col-md-2"><span class="chip">Пропущено: {{ $report['stats']['skipped'] ?? 0 }}</span></div>
                        <div class="col-md-2"><span class="chip">Ошибок: {{ $report['stats']['error_rows'] ?? 0 }}</span></div>
                    </div>

                    @if (!empty($report['errors']))
                        <div class="table-responsive">
                            <table class="table table-clean align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 120px">Строка</th>
                                        <th style="width: 280px">Ошибка</th>
                                        <th>Данные</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['errors'] as $err)
                                        <tr>
                                            <td>{{ $err['row'] ?? '—' }}</td>
                                            <td>{{ $err['message'] ?? '' }}</td>
                                            <td class="small">
                                                @if (is_array($err['data'] ?? null))
                                                    {{ json_encode($err['data'], JSON_UNESCAPED_UNICODE) }}
                                                @else
                                                    {{ $err['data'] ?? '' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif

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
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="dry_run" value="1" id="dry_groups">
                                <label class="form-check-label small" for="dry_groups">Сухой запуск (без записи в БД)</label>
                            </div>
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
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="dry_run" value="1" id="dry_teachers">
                                <label class="form-check-label small" for="dry_teachers">Сухой запуск (без записи в БД)</label>
                            </div>
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
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="dry_run" value="1" id="dry_assignments">
                                <label class="form-check-label small" for="dry_assignments">Сухой запуск (без записи в БД)</label>
                            </div>
                            <button class="btn btn-primary w-100">Загрузить</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
