@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Преподаватели</h3>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">+ Добавить</a>
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
                            <th>ФИО</th>
                            <th>Кафедра</th>
                            <th>Активен</th>
                            <th width="140">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teachers as $t)
                            <tr>
                                <td>{{ $t->id }}</td>
                                <td><b>{{ $t->fio }}</b></td>
                                <td>{{ $t->department }}</td>
                                <td>{{ $t->active ? 'Да' : 'Нет' }}</td>
                                <td>
                                    <a class="btn btn-sm btn-warning" href="{{ route('admin.teachers.edit', $t) }}">Ред.</a>
                                    <form method="POST" action="{{ route('admin.teachers.destroy', $t) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Удалить преподавателя?')"
                                            class="btn btn-sm btn-danger">X</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $teachers->links() }}
            </div>
        </div>
    </div>
@endsection
