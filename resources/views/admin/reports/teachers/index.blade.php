@extends('layouts.admin')
@section('header', 'Преподаватели')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>📊 Рейтинг преподавателей</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.teachers.export') }}" class="btn btn-success">⬇ Экспорт CSV</a>
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">Анкеты</a>
            </div>
        </div>


        <div class="alert alert-info">
            Здесь данные агрегируются <b>по всем группам</b> (единая таблица результатов по преподавателю).
        </div>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">

                @if ($result->count() === 0)
                    <div class="alert alert-warning mb-0">
                        Пока нет ответов для анализа.
                    </div>
                @else
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" class="row g-2 align-items-end">

                                <div class="col-md-3">
                                    <label class="form-label">Шаблон</label>
                                    <select name="template_id" class="form-select">
                                        <option value="">Все</option>
                                        @foreach ($templates as $t)
                                            <option value="{{ $t->id }}"
                                                {{ (string) $templateId === (string) $t->id ? 'selected' : '' }}>
                                                {{ $t->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Год</label>
                                    <input name="year" class="form-control" value="{{ $year }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Семестр</label>
                                    <input name="semester" class="form-control" value="{{ $semester }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Группа</label>
                                    <select name="group_id" class="form-select">
                                        <option value="">Все</option>
                                        @foreach ($groups as $g)
                                            <option value="{{ $g->id }}"
                                                {{ (string) $groupId === (string) $g->id ? 'selected' : '' }}>
                                                {{ $g->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Min n</label>
                                    <input name="min_n" type="number" class="form-control" value="{{ $minN }}">
                                </div>

                                <div class="col-md-12 d-flex gap-2 mt-2">
                                    <button class="btn btn-primary">Применить</button>
                                    <a class="btn btn-outline-secondary"
                                        href="{{ route('admin.reports.teachers.index') }}">Сброс</a>

                                    {{-- экспорт с теми же фильтрами --}}
                                    <a class="btn btn-success ms-auto"
                                        href="{{ route('admin.reports.teachers.export', request()->query()) }}">
                                        ⬇ Экспорт CSV
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Преподаватель</th>
                                <th style="width:140px;">Средний</th>
                                <th style="width:120px;">Ответов</th>
                                <th style="width:320px;">Диаграмма</th>
                                <th style="width:120px;">Открыть</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($result as $i => $row)
                                @php
                                    $percent = min(100, max(0, ($row['avg_score'] / 10) * 100));
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><b>{{ $row['teacher']->fio }}</b></td>
                                    <td class="text-center"><b>{{ $row['avg_score'] }}</b> / 10</td>
                                    <td class="text-center">{{ $row['answers_count'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 22px;">
                                            <div class="progress-bar" style="width: {{ $percent }}%;">
                                                {{ $row['avg_score'] }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-primary"
                                            href="{{ route('admin.reports.teachers.show', $row['teacher']->id) }}">
                                            Смотреть
                                        </a>
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
