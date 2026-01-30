@extends('layouts.admin')
@section('header', 'Рейтинг преподавателей')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3>👨‍🏫 {{ $teacher->fio }}</h3>
                <div class="text-muted">{{ $teacher->department }}</div>
            </div>

            <a href="{{ route('admin.reports.teachers.index') }}" class="btn btn-secondary">
                ← Назад к рейтингу
            </a>
            <a class="btn btn-success" href="{{ route('admin.reports.teachers.exportDetail', $teacher->id) }}">
                ⬇ Экспорт CSV
            </a>

        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-2">Общий средний балл</h5>

                @if ($overall === null)
                    <div class="alert alert-warning mb-0">
                        Пока нет оценок по этому преподавателю.
                    </div>
                @else
                    @php
                        $percent = min(100, max(0, ($overall / 10) * 100));
                    @endphp

                    <div class="d-flex justify-content-between">
                        <div><b>{{ $overall }}</b> / 100</div>
                        <div class="text-muted">ответов: {{ $overallCount }}</div>
                    </div>

                    <div class="progress mt-2" style="height: 26px;">
                        <div class="progress-bar" style="width: {{ $percent }}%;">
                            {{ $overall }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><b>Средние по вопросам</b></div>
            <div class="card-body table-responsive">

                @if ($perQuestionResult->count() === 0)
                    <div class="alert alert-warning mb-0">Нет данных</div>
                @else
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
                            @foreach ($perQuestionResult as $row)
                                @php
                                    $avg = $row['avg_score'];
                                    $percent = min(100, max(0, ($avg / 10) * 100));
                                @endphp

                                <tr>
                                    <td>
                                        <b>{{ $row['question']->code }}</b>
                                        — {{ $row['question']->text }}
                                    </td>
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
