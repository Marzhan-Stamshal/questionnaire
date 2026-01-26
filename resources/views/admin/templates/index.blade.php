@extends('layouts.admin')
@section('header', 'Шаблоны анкет')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Шаблоны анкет</h3>
            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">+ Создать</a>
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
                            <th>Название</th>
                            <th>Активен</th>
                            <th width="220">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($templates as $t)
                            <tr>
                                <td>{{ $t->id }}</td>
                                <td><b>{{ $t->title }}</b></td>
                                <td>{{ $t->is_active ? 'Да' : 'Нет' }}</td>
                                <td>
                                    <a class="btn btn-sm btn-warning"
                                        href="{{ route('admin.templates.edit', $t) }}">Открыть</a>
                                    <form method="POST" action="{{ route('admin.templates.destroy', $t) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Удалить шаблон?')"
                                            class="btn btn-sm btn-danger">X</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $templates->links() }}
            </div>
        </div>
    </div>
@endsection
