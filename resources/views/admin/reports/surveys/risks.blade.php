@extends('layouts.admin')
@section('header', 'Риски и нарушения')

@section('content')
    <div class="container-fluid">
        <div class="page-head">
            <div>
                <h4 class="page-title">Риски и нарушения по анкете #{{ $survey->id }}</h4>
                <div class="page-subtitle">
                    {{ $survey->group->kind_label ?? 'Группа' }}: <b>{{ $survey->group->name ?? '' }}</b> |
                    Шаблон: <b>{{ $survey->template->title ?? '' }}</b>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.surveys.answers', $survey->id) }}" class="btn btn-outline-primary">Ответы по вопросам</a>
                <a href="{{ route('admin.reports.surveys.show', $survey->id) }}" class="btn btn-outline-secondary">← Назад</a>
            </div>
        </div>

        @if ($riskQuestions->count() === 0)
            <div class="alert alert-info mb-0">
                В этой анкете не найдено риск-вопросов автоматически. Если нужно, добавим ручную пометку риск-вопросов в шаблоне.
            </div>
        @else
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                <div class="card soft-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Риск-вопросов</div>
                            <div class="fs-3 fw-bold">{{ $riskQuestions->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                <div class="card soft-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Ответов “Да”</div>
                            <div class="fs-3 fw-bold text-danger">{{ $totals['yes'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                <div class="card soft-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Всего ответов</div>
                            <div class="fs-3 fw-bold">{{ $totals['all'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                <div class="card soft-card h-100">
                        <div class="card-body">
                            <div class="text-muted small">Доля “Да”</div>
                            <div class="fs-3 fw-bold">{{ $totals['share'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card soft-card mb-3">
                <div class="card-header bg-danger-subtle text-danger-emphasis">
                    <b>Сводка по риск-вопросам</b>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-clean align-middle">
                        <thead>
                            <tr>
                                <th>Вопрос</th>
                                <th>Преподаватель</th>
                                <th>Да</th>
                                <th>Всего</th>
                                <th>Доля “Да”</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr class="{{ $row['yes_count'] > 0 ? 'table-danger' : '' }}">
                                    <td>
                                        <div class="small text-muted">{{ $row['question']->code }} | {{ $row['question']->type }}</div>
                                        <div>{{ $row['question']->text }}</div>
                                    </td>
                                    <td>{{ $row['teacher']->fio ?? '—' }}</td>
                                    <td><span class="badge text-bg-danger">{{ $row['yes_count'] }}</span></td>
                                    <td>{{ $row['total_count'] }}</td>
                                    <td>{{ $row['yes_share'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card soft-card">
                <div class="card-header"><b>Последние ответы “Да”</b></div>
                <div class="card-body table-responsive">
                    @if ($recentYes->count() === 0)
                        <div class="alert alert-success mb-0">Ответов “Да” по риск-вопросам не найдено.</div>
                    @else
                        <table class="table table-clean align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Сессия</th>
                                    <th>Когда</th>
                                    <th>Преподаватель</th>
                                    <th>Вопрос</th>
                                    <th>Комментарий</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentYes as $r)
                                    <tr class="table-danger">
                                        <td>#{{ $r->id }}</td>
                                        <td>
                                            @if ($r->respondent_session_id)
                                                <a href="{{ route('admin.reports.surveys.sessions.show', [$survey->id, $r->respondent_session_id]) }}"
                                                    class="fw-semibold text-decoration-none">
                                                    {{ $r->respondent_session_id }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ optional($r->respondentSession)->submitted_at ?? $r->created_at }}</td>
                                        <td>{{ $r->teacher->fio ?? '—' }}</td>
                                        <td>
                                            <div class="small text-muted">{{ $r->question->code ?? '' }}</div>
                                            <div>{{ $r->question->text ?? '' }}</div>
                                        </td>
                                        <td>{{ $r->value_text ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
