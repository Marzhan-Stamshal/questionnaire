@extends('layouts.admin')
@section('header', 'Ответы по вопросам')

@section('content')
    <div class="container-fluid">
        <div class="page-head">
            <div>
                <h4 class="page-title">Ответы по анкете #{{ $survey->id }}</h4>
                <div class="page-subtitle">
                    {{ $survey->group->kind_label ?? 'Группа' }}: <b>{{ $survey->group->name ?? '' }}</b> |
                    Шаблон: <b>{{ $survey->template->title ?? '' }}</b>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.surveys.show', $survey->id) }}" class="btn btn-outline-secondary">← Назад</a>
                <a href="{{ route('admin.reports.surveys.sessions', $survey->id) }}" class="btn btn-outline-primary">Сессии</a>
            </div>
        </div>

        <div class="card soft-card sticky-filters mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label mb-1">Вопрос</label>
                        <select name="question_id" class="form-select">
                            <option value="">Все вопросы</option>
                            @foreach ($questions as $q)
                                <option value="{{ $q->id }}" {{ (string) $questionId === (string) $q->id ? 'selected' : '' }}>
                                    {{ $q->code }} | {{ $q->type }} | {{ \Illuminate\Support\Str::limit($q->text, 120) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label mb-1">Преподаватель</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">Все преподаватели</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}" {{ (string) $teacherId === (string) $t->id ? 'selected' : '' }}>
                                    {{ $t->fio }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" value="1" id="only_yes" name="only_yes"
                                {{ $onlyYes ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="only_yes">Только “Да”</label>
                        </div>
                    </div>

                    <div class="col-lg-2 d-flex gap-2">
                        <button class="btn btn-primary w-100">Применить</button>
                        <a href="{{ route('admin.reports.surveys.answers', $survey->id) }}"
                            class="btn btn-outline-secondary w-100">Сброс</a>
                    </div>
                </form>
            </div>
        </div>

        @if ($selectedQuestion && $questionStats)
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Всего ответов</div>
                            <div class="fs-3 fw-bold">{{ $questionStats['total'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Ответ “Да”</div>
                            <div class="fs-3 fw-bold text-danger">{{ $questionStats['yes'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Ответ “Нет”</div>
                            <div class="fs-3 fw-bold">{{ $questionStats['no'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Доля “Да”</div>
                            <div class="fs-3 fw-bold">{{ $questionStats['yes_percent'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body table-responsive">
                @if ($rows->count() === 0)
                    <div class="alert alert-warning mb-0">По выбранным фильтрам ответов не найдено.</div>
                @else
                    <table class="table table-clean align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Сессия</th>
                                <th>Когда</th>
                                <th>Преподаватель</th>
                                <th>Вопрос</th>
                                <th>Ответ</th>
                                <th>Комментарий</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $r)
                                @php
                                    $isYes = !is_null($r->value_int) && (int) $r->value_int === 1;
                                    $qType = $r->question->type ?? '';
                                @endphp
                                <tr class="{{ $isYes ? 'table-danger' : '' }}">
                                    <td>#{{ $r->id }}</td>
                                    <td>
                                        @if ($r->respondent_session_id)
                                            <a href="{{ route('admin.reports.surveys.sessions.show', [$survey->id, $r->respondent_session_id]) }}"
                                                class="fw-semibold text-decoration-none">
                                                {{ $r->respondent_session_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($r->respondentSession)->submitted_at ?? $r->created_at }}</td>
                                    <td>{{ $r->teacher->fio ?? '—' }}</td>
                                    <td>
                                        <div class="small text-muted">{{ $r->question->code ?? '' }} | {{ $qType }}</div>
                                        <div>{{ $r->question->text ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if (!is_null($r->value_int))
                                            @if ($qType === 'yes_no' || $qType === 'yes_no_with_text')
                                                <span class="badge {{ (int) $r->value_int === 1 ? 'text-bg-danger' : 'text-bg-secondary' }}">
                                                    {{ (int) $r->value_int === 1 ? 'Да' : 'Нет' }}
                                                </span>
                                            @else
                                                <span class="badge text-bg-primary">{{ $r->value_int }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 280px;">
                                        @if (filled($r->value_text))
                                            @php $arr = json_decode($r->value_text, true); @endphp
                                            @if (is_array($arr))
                                                {{ implode(', ', $arr) }}
                                            @else
                                                {{ $r->value_text }}
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $rows->links() }}
                @endif
            </div>
        </div>

        <div class="small text-muted mt-3">
            Важно: система остаётся анонимной. Здесь отображается номер сессии, а не личность студента.
        </div>
    </div>
@endsection
