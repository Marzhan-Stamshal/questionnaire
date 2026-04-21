@extends('layouts.admin')
@section('header', 'Группы')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Группы</h3>
            <a href="{{ route('admin.groups.create') }}" class="btn btn-primary">+ Добавить</a>
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
                            <th>Тип</th>
                            <th>Факультет</th>
                            <th>ОП</th>
                            <th>Курс</th>
                            <th>Активна</th>
                            <th width="140">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $g)
                            <tr>
                                <td>{{ $g->id }}</td>
                                <td><b>{{ $g->name }}</b></td>
                                <td>{{ $g->kind_label }}</td>
                                <td>{{ $g->faculty }}</td>
                                <td>{{ $g->program }}</td>
                                <td>{{ $g->course }}</td>
                                <td>{{ $g->active ? 'Да' : 'Нет' }}</td>
                                <td>
                                    <a class="btn btn-sm btn-warning" href="{{ route('admin.groups.edit', $g) }}">Ред.</a>
                                    <form method="POST" action="{{ route('admin.groups.destroy', $g) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Удалить группу?')"
                                            class="btn btn-sm btn-danger">X</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $groups->links() }}
            </div>
        </div>
    </div>
@endsection
