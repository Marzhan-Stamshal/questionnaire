@extends('layouts.admin')
@section('header', 'Матрица оценок')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3>🔥 Матрица оценок анкеты #{{ $survey->id }}</h3>
                <div class="text-muted">
                    Группа: <b>{{ $survey->group->name ?? '' }}</b> |
                    Шаблон: <b>{{ $survey->template->title ?? '' }}</b>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.surveys.exportMatrix', $survey->id) }}" class="btn btn-success">⬇ Matrix
                    CSV</a>
                <a href="{{ route('admin.reports.surveys.show', $survey->id) }}" class="btn btn-secondary">
                    ← Назад
                </a>

            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">

                @if ($matrixQuestions->count() === 0 || $teachers->count() === 0)
                    <div class="alert alert-warning mb-0">
                        Нет матричных вопросов или нет преподавателей у группы.
                    </div>
                @else
                    <style>
                        .cell-box {
                            border-radius: 10px;
                            padding: 8px;
                            text-align: center;
                            font-weight: 600;
                            color: #fff;
                            min-width: 70px;
                        }

                        .cell-sub {
                            font-size: 11px;
                            opacity: .9;
                            font-weight: 400;
                        }
                    </style>

                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 340px;">Вопрос</th>

                                @foreach ($teachers as $t)
                                    <th class="text-center" style="min-width: 140px;">
                                        <a href="{{ route('admin.reports.surveys.teacher', [$survey->id, $t->id]) }}">
                                            {{ $t->fio }}
                                        </a>

                                    </th>
                                @endforeach

                                <th class="text-center" style="min-width: 140px;">Средний по вопросу</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($matrixQuestions as $q)
                                <tr>
                                    <td>
                                        <b>{{ $q->code }}</b> — {{ $q->text }}
                                    </td>

                                    @foreach ($teachers as $t)
                                        @php
                                            $cell = $matrix[$q->id][$t->id] ?? null;
                                            $avg = $cell ? $cell['avg'] : null;
                                            $count = $cell ? $cell['count'] : 0;

                                            // Цвет: 0..10 -> красный..зелёный
                                            // делаем через HSL (0=0 красный, 10=120 зелёный)
                                            if ($avg === null) {
                                                $bg = '#9aa0a6'; // серый
                                            } else {
                                                $hue = (int) (($avg / 10) * 120);
                                                $bg = "hsl($hue, 75%, 45%)";
                                            }
                                        @endphp

                                        <td class="text-center">
                                            <div class="cell-box" style="background: {{ $bg }};">
                                                {{ $avg === null ? '—' : $avg }}
                                                <div class="cell-sub">n={{ $count }}</div>
                                            </div>
                                        </td>
                                    @endforeach

                                    @php
                                        $qa = $questionAverages[$q->id] ?? null;
                                    @endphp
                                    <td class="text-center">
                                        @if ($qa)
                                            <b>{{ $qa['avg'] }}</b>
                                            <div class="text-muted small">n={{ $qa['count'] }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot class="table-light">
                            <tr>
                                <th>Средний по преподавателю</th>
                                @foreach ($teachers as $t)
                                    @php
                                        $ta = $teacherAverages[$t->id] ?? null;
                                    @endphp
                                    <th class="text-center">
                                        @if ($ta)
                                            <b>{{ $ta['avg'] }}</b>
                                            <div class="text-muted small">n={{ $ta['count'] }}</div>
                                        @else
                                            —
                                        @endif
                                    </th>
                                @endforeach
                                <th class="text-center">—</th>
                            </tr>
                        </tfoot>

                    </table>
                @endif

            </div>
        </div>

    </div>
@endsection
