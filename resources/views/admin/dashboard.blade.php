@extends('layouts.admin')

@section('title', 'Главная')
@section('header', 'Главная')

@section('content')
    <div class="container-fluid">

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Активные анкеты</div>
                        <div class="fs-3 fw-bold">{{ $activeSurveys }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Всего анкет</div>
                        <div class="fs-3 fw-bold">{{ $totalSurveys }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Групп</div>
                        <div class="fs-3 fw-bold">{{ $groupsCount }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Преподавателей</div>
                        <div class="fs-3 fw-bold">{{ $teachersCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">


            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Ответили</div>
                        <div class="fs-3 fw-bold">{{ $uniqueSessions }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Групп без анкет</div>
                        <div class="fs-3 fw-bold">{{ $groupsWithoutSurvey }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header"><b>Топ преподавателей (n≥5)</b></div>
                    <div class="card-body">
                        @if ($topTeachers->count() === 0)
                            <div class="text-muted">Пока нет данных</div>
                        @else
                            @foreach ($topTeachers as $row)
                                @php $p = min(100, max(0, ($row['avg_score']/10)*100)); @endphp
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div><b>{{ $row['teacher']->fio }}</b></div>
                                        <div>{{ $row['avg_score'] }} / 100 (n={{ $row['answers_count'] }})</div>
                                    </div>
                                    <div class="progress" style="height: 18px;">
                                        <div class="progress-bar" style="width: {{ $p }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                            <a class="btn btn-sm btn-primary mt-2"
                                href="{{ route('admin.reports.teachers.index') }}">Открыть отчёт</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header"><b>Быстрые действия</b></div>
                    <div class="card-body d-flex flex-wrap gap-2">
                        <a class="btn btn-dark" href="{{ route('admin.surveys.bulk.create') }}">⚡ Массово создать
                            анкеты</a>
                        <a class="btn btn-secondary" href="{{ route('admin.surveys.index') }}">🧾 Анкеты</a>
                        <a class="btn btn-secondary" href="{{ route('admin.templates.index') }}">🧩 Шаблоны</a>
                        <a class="btn btn-secondary" href="{{ route('admin.import.index') }}">📥 Импорт</a>
                        <a class="btn btn-secondary" href="{{ route('admin.reports.teachers.index') }}">📊 Рейтинг</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
