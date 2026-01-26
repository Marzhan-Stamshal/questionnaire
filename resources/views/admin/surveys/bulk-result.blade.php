@extends('layouts.admin')
@section('header', 'Результат массового создания')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>✅ Созданные анкеты</h3>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">К анкетам</a>
        </div>

        <div class="alert alert-info">
            Создано: <b>{{ $createdSurveys->count() }}</b> |
            Пропущено (уже существовали): <b>{{ $skipped }}</b>
        </div>

        @if ($createdSurveys->count() === 0)
            <div class="alert alert-warning">Ничего не создано.</div>
        @else
            <div class="card shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Группа</th>
                                <th>Шаблон</th>
                                <th>Ссылка</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($createdSurveys as $s)
                                <tr>
                                    <td>{{ $s->id }}</td>
                                    <td><b>{{ $s->group->name ?? '' }}</b></td>
                                    <td>{{ $s->template->title ?? '' }}</td>
                                    <td>
                                        <a target="_blank" href="{{ url('/s/' . $s->public_token) }}">
                                            {{ url('/s/' . $s->public_token) }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
