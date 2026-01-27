@extends('layouts.admin')
@section('header', 'Анкеты')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Анкеты (по группам)</h3>
            <a href="{{ route('admin.surveys.bulk.create') }}" class="btn btn-dark">⚡ Массово создать для всех групп</a>

            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary">+ Создать</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Группа</th>
                            <th>Шаблон</th>
                            <th>Год</th>
                            <th>Семестр</th>
                            <th>Статус</th>
                            <th>Ссылка</th>
                            <th width="120">Действия</th>
                            <th>Отчёт</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($surveys as $s)
                            <tr>
                                <td>{{ $s->id }}</td>
                                <td><b>{{ $s->group->name ?? '' }}</b></td>
                                <td>{{ $s->template->title ?? '' }}</td>
                                <td>{{ $s->year }}</td>
                                <td>{{ $s->semester }}</td>

                                <td>
                                    @if ($s->status === 'active')
                                        <span class="badge bg-success">active</span>
                                    @elseif($s->status === 'draft')
                                        <span class="badge bg-secondary">draft</span>
                                    @else
                                        <span class="badge bg-danger">closed</span>
                                    @endif
                                </td>

                                <td style="min-width: 260px;">
                                    <a target="_blank" href="{{ url('/s/' . $s->public_token) }}">
                                        {{ url('/s/' . $s->public_token) }}
                                    </a>
                                </td>

                                <td>
                                    <a class="btn btn-sm btn-warning" href="{{ route('admin.surveys.edit', $s) }}">Ред.</a>
                                    <form method="POST" action="{{ route('admin.surveys.destroy', $s) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Удалить анкету?')"
                                            class="btn btn-sm btn-danger">X</button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-dark"
                                        href="{{ route('admin.reports.surveys.show', $s->id) }}">
                                        📊
                                    </a>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $surveys->links() }}
            </div>
        </div>
    </div>
@endsection
