@extends('layouts.admin')
@section('header', 'Преподаватель в анкете')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3>👨‍🏫 {{ $teacher->fio }}</h3>
                <div class="text-muted">
                    Анкета #{{ $survey->id }} | Группа: <b>{{ $survey->group->name ?? '' }}</b>
                </div>
            </div>
            <a href="{{ route('admin.reports.surveys.matrix', $survey->id) }}" class="btn btn-secondary">
                ← Назад к матрице
            </a>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5>Общий балл (в рамках этой анкеты)</h5>

                @if ($overall === null)
                    <div class="alert alert-warning mb-0">Нет данных по преподавателю в этой анкете</div>
                @else
                    @php $percent = min(100, max(0, ($overall / 10) * 100)); @endphp

                    <div class="d-flex justify-content-between">
                        <div><b>{{ $overall }}</b> / 10</div>
                        <div class="text-muted">ответов: {{ $overallCount }}</div>
                    </div>

                    <div class="progress mt-2" style="height: 26px;">
                        <div class="progress-bar" style="width: {{ $percent }}%;">{{ $overall }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><b>Средние по вопросам</b></div>
            <div class="card-body table-responsive">

                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Вопрос</th>
                            <th style="width:140px;">Средний</th>
                            <th style="width:120px;">Ответов</th>
                            <th style="width:320px;">Диаграмма</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($matrixQuestions as $q)
                            @php
                                $item = $map[$q->id] ?? null;
                                $avg = $item['avg'] ?? null;
                                $count = $item['count'] ?? 0;
                                $percent = $avg !== null ? min(100, max(0, ($avg / 10) * 100)) : 0;
                            @endphp

                            <tr>
                                <td><b>{{ $q->code }}</b> — {{ $q->text }}</td>
                                <td class="text-center">{{ $avg === null ? '—' : $avg }}</td>
                                <td class="text-center">{{ $count }}</td>
                                <td>
                                    @if ($avg === null)
                                        —
                                    @else
                                        <div class="progress" style="height: 22px;">
                                            <div class="progress-bar" style="width: {{ $percent }}%;">
                                                {{ $avg }}</div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    </div>
@endsection
