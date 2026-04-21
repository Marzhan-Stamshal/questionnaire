@extends('layouts.admin')
@section('header', 'Отчёт анкеты')

@section('content')
    <div class="container">

        <div class="page-head">
            <div>
                <h3 class="page-title">Отчёт анкеты #{{ $survey->id }}</h3>
                <div class="page-subtitle">
                    {{ $survey->group->kind_label ?? 'Группа' }}: <b>{{ $survey->group->name ?? '' }}</b> |
                    Шаблон: <b>{{ $survey->template->title ?? '' }}</b>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.surveys.matrix', $survey->id) }}" class="btn btn-primary">🔥 Матрица</a>

                {{-- <a href="{{ route('admin.reports.surveys.comments', $survey->id) }}" class="btn btn-dark">💬 Комментарии</a> --}}
                <a class="btn btn-outline-primary" href="{{ route('admin.reports.surveys.sessions', $survey->id) }}">
                    👤 Ответы по респондентам
                </a>
                <a class="btn btn-dark" href="{{ route('admin.reports.surveys.answers', $survey->id) }}">
                    🔎 Ответы по вопросам
                </a>
                <a class="btn btn-danger" href="{{ route('admin.reports.surveys.risks', $survey->id) }}">
                    🚨 Риски/Нарушения
                </a>
                <a href="{{ route('admin.reports.surveys.exportRaw', $survey->id) }}" class="btn btn-success">⬇ Raw CSV</a>
                <a class="btn btn-success" href="{{ route('admin.exports.responses.csv', ['survey_id' => $survey->id]) }}">
                    ⬇ Экспорт ответов (CSV)
                </a>
                <a class="btn btn-success" href="{{ route('admin.exports.responses.csv') }}">
                    ⬇ Экспорт всех ответов (CSV)
                </a>

                <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">Анкеты</a>
            </div>
        </div>

        <div class="mb-3">
            <span class="chip">Сводка по анкете</span>
            <span class="chip">Сравнение преподавателей</span>
            <span class="chip">Средние по вопросам</span>
        </div>

        {{-- Сравнение преподавателей внутри анкеты --}}
        <div class="card soft-card mb-3">
            <div class="card-header"><b>Сравнение преподавателей (внутри этой группы)</b></div>
            <div class="card-body">

                @if ($teacherResult->count() === 0)
                    <div class="alert alert-warning mb-0">Нет данных</div>
                @else
                    @foreach ($teacherResult as $row)
                        @php
                            $percent = min(100, max(0, ($row['avg_score'] / 10) * 100));
                        @endphp

                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <div><b>{{ $row['teacher']->fio }}</b></div>
                                <div>{{ $row['avg_score'] }} / 10 (ответов: {{ $row['answers_count'] }})</div>
                            </div>
                            <div class="progress" style="height: 22px;">
                                <div class="progress-bar" style="width: {{ $percent }}%;">
                                    {{ $row['avg_score'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>
        </div>

        {{-- Средние по вопросам --}}
        <div class="card soft-card">
            <div class="card-header"><b>Средние по вопросам (по всей анкете)</b></div>
            <div class="card-body table-responsive">

                @if ($questionResult->count() === 0)
                    <div class="alert alert-warning mb-0">Нет данных</div>
                @else
                    <table class="table table-clean align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Вопрос</th>
                                <th style="width:140px;">Средний</th>
                                <th style="width:120px;">Ответов</th>
                                <th style="width:320px;">Диаграмма</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questionResult as $row)
                                @php
                                    $avg = $row['avg_score'];
                                    $percent = min(100, max(0, ($avg / 10) * 100));
                                @endphp
                                <tr>
                                    <td><b>{{ $row['question']->code }}</b> — {{ $row['question']->text }}</td>
                                    <td class="text-center"><b>{{ $avg }}</b> / 100</td>
                                    <td class="text-center">{{ $row['answers_count'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 22px;">
                                            <div class="progress-bar" style="width: {{ $percent }}%;">
                                                {{ $avg }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            </div>
        </div>

    </div>
@endsection
