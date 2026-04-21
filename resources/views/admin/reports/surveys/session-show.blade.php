@extends('layouts.admin')
@section('header', 'Ответы одного респондента')

@section('content')
    <div class="container-fluid">
        <div class="page-head">
            <div>
                <h4 class="page-title">Анкета #{{ $survey->id }} — сессия #{{ $session->id }}</h4>
                <div class="page-subtitle">Отправлено: <b>{{ $session->submitted_at }}</b></div>
            </div>
            <a class="btn btn-secondary" href="{{ route('admin.reports.surveys.sessions', $survey->id) }}">← К списку
                сессий</a>
        </div>

        <div class="card soft-card">
            <div class="card-body">
                @foreach ($responses as $r)
                    <div class="soft-card p-3 mb-2">
                        <div class="small text-muted">
                            Вопрос #{{ $r->question_id }} @if ($r->teacher)
                                | Преподаватель: <b>{{ $r->teacher->fio }}</b>
                            @endif
                        </div>

                        <div class="fw-bold">{{ $r->question->text ?? '' }}</div>

                        <div class="mt-2">
                            @if (!is_null($r->value_int))
                                <span class="badge bg-primary">{{ $r->value_int }}</span>
                            @endif

                            @if (!is_null($r->value_text) && $r->value_text !== '')
                                @php $arr = json_decode($r->value_text, true); @endphp
                                @if (is_array($arr))
                                    <ul class="mb-0">
                                        @foreach ($arr as $a)
                                            <li>{{ $a }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div>{{ $r->value_text }}</div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
