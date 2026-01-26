@extends('layouts.admin')
@section('header', 'Сессии ответов анкеты')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h4 class="mb-0">Сессии анкеты #{{ $survey->id }}</h4>
                <div class="text-muted">Группа: <b>{{ $survey->group->name ?? '' }}</b> | Шаблон:
                    <b>{{ $survey->template->title ?? '' }}</b></div>
            </div>
            <a class="btn btn-secondary" href="{{ url()->previous() }}">← Назад</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID сессии</th>
                            <th>Когда отправлено</th>
                            <th>Ответов</th>
                            <th width="160">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $s)
                            <tr>
                                <td>{{ $s->id }}</td>
                                <td>{{ $s->submitted_at }}</td>
                                <td>{{ $counts[$s->id] ?? 0 }}</td>
                                <td>
                                    <a class="btn btn-sm btn-primary"
                                        href="{{ route('admin.reports.surveys.sessions.show', [$survey->id, $s->id]) }}">
                                        Смотреть ответы
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $sessions->links() }}
            </div>
        </div>
    </div>
@endsection
