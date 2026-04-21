@extends('layouts.admin')
@section('header', 'Единая аналитика')

@section('content')
    <div class="container-fluid">
        <div class="page-head">
            <div>
                <h4 class="page-title">Единая аналитика ответов</h4>
                <div class="page-subtitle">Фильтруйте по анкете, вопросу, преподавателю, периоду и типу ответа.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('admin.reports.analytics.index') }}">Сбросить всё</a>
                <a class="btn btn-success"
                    href="{{ route('admin.exports.responses.csv', request()->only(['survey_id', 'group_id', 'year', 'semester'])) }}">
                    Экспорт CSV
                </a>
            </div>
        </div>

        <div class="card soft-card sticky-filters mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-xl-2 col-md-4">
                        <label class="form-label mb-1">Анкета</label>
                        <select name="survey_id" class="form-select">
                            <option value="">Все</option>
                            @foreach ($surveys as $s)
                                <option value="{{ $s->id }}" {{ (string) $surveyId === (string) $s->id ? 'selected' : '' }}>
                                    #{{ $s->id }} | {{ $s->group->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <label class="form-label mb-1">Группа/цикл</label>
                        <select name="group_id" class="form-select">
                            <option value="">Все</option>
                            @foreach ($groups as $g)
                                <option value="{{ $g->id }}" {{ (string) $groupId === (string) $g->id ? 'selected' : '' }}>
                                    {{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <label class="form-label mb-1">Шаблон</label>
                        <select name="template_id" class="form-select">
                            <option value="">Все</option>
                            @foreach ($templates as $t)
                                <option value="{{ $t->id }}" {{ (string) $templateId === (string) $t->id ? 'selected' : '' }}>
                                    {{ $t->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="form-label mb-1">Преподаватель</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">Все</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}" {{ (string) $teacherId === (string) $t->id ? 'selected' : '' }}>
                                    {{ $t->fio }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="form-label mb-1">Тип вопроса</label>
                        <select name="question_type" class="form-select">
                            <option value="">Все</option>
                            @foreach (['scale_0_10', 'yes_no', 'yes_no_with_text', 'text', 'single_choice', 'multiple_choice'] as $type)
                                <option value="{{ $type }}" {{ $questionType === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-12">
                        <label class="form-label mb-1">Вопрос</label>
                        <select name="question_id" class="form-select">
                            <option value="">Все</option>
                            @foreach ($questions as $q)
                                <option value="{{ $q->id }}" {{ (string) $questionId === (string) $q->id ? 'selected' : '' }}>
                                    {{ $q->code }} | {{ \Illuminate\Support\Str::limit($q->text, 70) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-1 col-md-3">
                        <label class="form-label mb-1">Год</label>
                        <input name="year" type="number" class="form-control" value="{{ $year }}">
                    </div>
                    <div class="col-xl-2 col-md-3">
                        <label class="form-label mb-1">Семестр</label>
                        <select name="semester" class="form-select">
                            <option value="">Все</option>
                            <option value="1" {{ (string) $semester === '1' ? 'selected' : '' }}>1</option>
                            <option value="2" {{ (string) $semester === '2' ? 'selected' : '' }}>2</option>
                            <option value="autumn" {{ $semester === 'autumn' ? 'selected' : '' }}>autumn</option>
                            <option value="spring" {{ $semester === 'spring' ? 'selected' : '' }}>spring</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-3">
                        <label class="form-label mb-1">Ответ</label>
                        <select name="answer_mode" class="form-select">
                            <option value="">Любой</option>
                            <option value="yes" {{ $answerMode === 'yes' ? 'selected' : '' }}>Только Да (1)</option>
                            <option value="no" {{ $answerMode === 'no' ? 'selected' : '' }}>Только Нет (0)</option>
                            <option value="with_text" {{ $answerMode === 'with_text' ? 'selected' : '' }}>Только с текстом</option>
                            <option value="with_int" {{ $answerMode === 'with_int' ? 'selected' : '' }}>Только числовые</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-3">
                        <label class="form-label mb-1">Дата от</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-xl-2 col-md-3">
                        <label class="form-label mb-1">Дата до</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-xl-3 col-md-8">
                        <label class="form-label mb-1">Поиск</label>
                        <input type="text" name="search" class="form-control" value="{{ $search }}"
                            placeholder="Текст ответа, код вопроса, ФИО...">
                    </div>
                    <div class="col-xl-2 col-md-4 d-grid">
                        <button class="btn btn-primary">Применить фильтры</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-2 col-md-4">
                <div class="card soft-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Ответов</div>
                        <div class="fs-3 fw-bold">{{ $totalResponses }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <div class="card soft-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Сессий</div>
                        <div class="fs-3 fw-bold">{{ $uniqueSessions }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <div class="card soft-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Да (1)</div>
                        <div class="fs-3 fw-bold text-danger">{{ $yesCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <div class="card soft-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Нет (0)</div>
                        <div class="fs-3 fw-bold">{{ $noCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <div class="card soft-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Средний балл</div>
                        <div class="fs-3 fw-bold">{{ is_null($avgScore) ? '—' : round($avgScore, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <div class="card soft-card h-100">
                    <div class="card-body">
                        <div class="small text-muted">Доля Да</div>
                        <div class="fs-3 fw-bold">
                            {{ $totalResponses > 0 ? round(($yesCount / $totalResponses) * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card soft-card mb-3">
            <div class="card-header"><b>Топ вопросов по количеству ответов</b></div>
            <div class="card-body">
                @if ($topQuestionStats->count() === 0)
                    <div class="text-muted">Нет данных</div>
                @else
                    @foreach ($topQuestionStats as $q)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>{{ \Illuminate\Support\Str::limit($q['label'], 150) }}</div>
                            <b>{{ $q['count'] }}</b>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="card soft-card">
            <div class="card-body table-responsive">
                @if ($rows->count() === 0)
                    <div class="alert alert-warning mb-0">По выбранным фильтрам записей не найдено.</div>
                @else
                    <table class="table table-clean align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Анкета</th>
                                <th>Сессия</th>
                                <th>Преподаватель</th>
                                <th>Вопрос</th>
                                <th>Int</th>
                                <th>Text</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $r)
                                @php
                                    $qType = $r->question->type ?? '';
                                    $isYesNo = in_array($qType, ['yes_no', 'yes_no_with_text']);
                                @endphp
                                <tr class="{{ !is_null($r->value_int) && (int) $r->value_int === 1 && $isYesNo ? 'table-danger' : '' }}">
                                    <td>#{{ $r->id }}</td>
                                    <td>
                                        <div class="small text-muted">#{{ $r->survey_id }}</div>
                                        <div>{{ $r->survey->group->name ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if ($r->respondent_session_id)
                                            <a href="{{ route('admin.reports.surveys.sessions.show', [$r->survey_id, $r->respondent_session_id]) }}"
                                                class="text-decoration-none fw-semibold">
                                                {{ $r->respondent_session_id }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $r->teacher->fio ?? '—' }}</td>
                                    <td>
                                        <div class="small text-muted">{{ $r->question->code ?? '' }} | {{ $qType }}</div>
                                        <div>{{ $r->question->text ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if (!is_null($r->value_int))
                                            @if ($isYesNo)
                                                <span class="badge {{ (int) $r->value_int === 1 ? 'text-bg-danger' : 'text-bg-secondary' }}">
                                                    {{ (int) $r->value_int === 1 ? 'Да' : 'Нет' }}
                                                </span>
                                            @else
                                                <span class="badge text-bg-primary">{{ $r->value_int }}</span>
                                            @endif
                                        @else
                                            —
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
                                    <td>{{ $r->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $rows->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection
