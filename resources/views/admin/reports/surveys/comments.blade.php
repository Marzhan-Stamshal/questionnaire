@extends('layouts.admin')
@section('header', 'Комментарии анкеты')

@section('content')
    <div class="container">

        <div class="page-head">
            <div>
                <h3 class="page-title">Комментарии анкеты #{{ $survey->id }}</h3>
                <div class="page-subtitle">
                    Группа: <b>{{ $survey->group->name ?? '' }}</b> |
                    Шаблон: <b>{{ $survey->template->title ?? '' }}</b>
                </div>
            </div>

            <a href="{{ route('admin.reports.surveys.show', $survey->id) }}" class="btn btn-secondary">
                ← Назад
            </a>
        </div>

        <div class="card soft-card">
            <div class="card-body">

                @if ($rows->count() === 0)
                    <div class="alert alert-warning mb-0">Комментариев нет</div>
                @else
                    @foreach ($rows as $r)
                        <div class="soft-card p-3 mb-2">
                            <div class="small text-muted">
                                #{{ $r->id }} | {{ $r->created_at }}
                            </div>

                            <div class="mt-1">
                                <b>{{ $r->question->code ?? '' }}</b>
                                — {{ $r->question->text ?? '' }}
                            </div>

                            @if ($r->teacher)
                                <div class="text-muted small">
                                    Преподаватель: <b>{{ $r->teacher->fio }}</b>
                                </div>
                            @endif

                            <div class="mt-2">
                                @if (($r->question->type ?? '') === 'multiple_choice')
                                    @php
                                        $answers = json_decode($r->value_text, true);
                                    @endphp

                                    @if (is_array($answers))
                                        <ul class="mb-0">
                                            @foreach ($answers as $a)
                                                <li>{{ $a }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        {{ $r->value_text }}
                                    @endif
                                @else
                                    {{ $r->value_text }}
                                @endif
                            </div>

                        </div>
                    @endforeach

                    {{ $rows->links() }}
                @endif

            </div>
        </div>

    </div>
@endsection
