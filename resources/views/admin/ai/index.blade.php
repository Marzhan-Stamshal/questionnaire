@extends('layouts.admin')
@section('header', 'ИИ ассистент')

@section('content')
    <div class="container-fluid">
        <div class="page-head">
            <div>
                <h4 class="page-title">ИИ-ассистент для анализа анкет</h4>
                <div class="page-subtitle">Локальный анализ через Ollama. Данные не отправляются во внешний облачный API.</div>
            </div>
        </div>

        <div class="card soft-card mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    @if (($health['ok'] ?? false) === true)
                        <span class="badge text-bg-success">Ollama: online</span>
                    @else
                        <span class="badge text-bg-danger">Ollama: offline</span>
                    @endif
                    <div class="small text-muted mt-2">{{ $health['message'] ?? '' }}</div>
                </div>
                <div class="small text-muted">
                    Endpoint: <code>{{ config('services.ai.endpoint') }}</code><br>
                    Model: <code>{{ config('services.ai.model') }}</code>
                </div>
            </div>
            @if (!empty($health['models']))
                <div class="card-footer bg-transparent">
                    <div class="small text-muted mb-1">Доступные модели:</div>
                    @foreach ($health['models'] as $model)
                        <span class="chip">{{ $model }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card soft-card mb-3">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.ai.summarize') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-8">
                        <label class="form-label mb-1">Анкета</label>
                        <select name="survey_id" class="form-select" required>
                            <option value="">Выберите анкету</option>
                            @foreach ($surveys as $s)
                                <option value="{{ $s->id }}" {{ (string) $surveyId === (string) $s->id ? 'selected' : '' }}>
                                    #{{ $s->id }} | {{ $s->group->name ?? '' }} | {{ $s->template->title ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button class="btn btn-primary">Сделать ИИ-сводку по рискам</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($aiError)
            <div class="alert alert-danger">{{ $aiError }}</div>
        @endif

        @if ($aiResult)
            <div class="card soft-card">
                <div class="card-header"><b>Результат ИИ-анализа</b></div>
                <div class="card-body">
                    <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;">{{ $aiResult }}</pre>
                </div>
            </div>
        @endif
    </div>
@endsection
