@extends('layouts.admin')
@section('header', 'Связи групп и преподавателей')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Связи: группа ↔ преподаватель</h3>
            <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary">+ Добавить</a>
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
                            <th>Преподаватель</th>
                            <th>Год</th>
                            <th>Семестр</th>
                            <th width="140">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assignments as $a)
                            <tr>
                                <td>{{ $a->id }}</td>
                                <td><b>{{ $a->group->name ?? '' }}</b></td>
                                <td>{{ $a->teacher->fio ?? '' }}</td>
                                <td>{{ $a->year }}</td>
                                <td>{{ $a->semester }}</td>
                                <td>
                                    <a class="btn btn-sm btn-warning"
                                        href="{{ route('admin.assignments.edit', $a) }}">Ред.</a>
                                    <form method="POST" action="{{ route('admin.assignments.destroy', $a) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Удалить связь?')"
                                            class="btn btn-sm btn-danger">X</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $assignments->links() }}
            </div>
        </div>
    </div>
@endsection
