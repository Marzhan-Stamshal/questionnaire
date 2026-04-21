@extends('layouts.admin')
@section('header', 'Журнал аудита')

@section('content')
    <div class="container-fluid">
        <div class="page-head">
            <div>
                <h4 class="page-title">Журнал действий админов</h4>
                <div class="page-subtitle">Фиксируются переходы, фильтры, экспорты и чувствительные действия.</div>
            </div>
        </div>

        <div class="card soft-card sticky-filters mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label mb-1">Админ</label>
                        <select name="user_id" class="form-select">
                            <option value="">Все</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" {{ (string) $userId === (string) $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">Route</label>
                        <input type="text" name="route_name" class="form-control" value="{{ $routeName }}">
                    </div>

                    <div class="col-md-1">
                        <label class="form-label mb-1">Method</label>
                        <select name="method" class="form-select">
                            <option value="">Все</option>
                            @foreach (['GET', 'POST', 'PUT', 'DELETE'] as $m)
                                <option value="{{ $m }}" {{ $method === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label mb-1">Status</label>
                        <input type="number" name="status" class="form-control" value="{{ $status }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">Дата от</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">Дата до</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1">Поиск</label>
                        <input type="text" name="search" class="form-control" value="{{ $search }}"
                            placeholder="path / email / ip">
                    </div>

                    <div class="col-md-12 d-flex gap-2 mt-2">
                        <button class="btn btn-primary">Применить</button>
                        <a href="{{ route('admin.audit.logs.index') }}" class="btn btn-outline-secondary">Сброс</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card soft-card">
            <div class="card-body table-responsive">
                <table class="table table-clean align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Когда</th>
                            <th>Админ</th>
                            <th>Запрос</th>
                            <th>Route</th>
                            <th>IP</th>
                            <th>Params</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>#{{ $log->id }}</td>
                                <td>{{ $log->created_at }}</td>
                                <td>
                                    {{ $log->user->name ?? '—' }}<br>
                                    <span class="small text-muted">{{ $log->user->email ?? '' }}</span>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border">{{ $log->method }}</span>
                                    <span class="badge {{ (int) $log->status_code >= 400 ? 'text-bg-danger' : 'text-bg-success' }}">
                                        {{ $log->status_code ?? '—' }}
                                    </span>
                                    <div class="small mt-1">{{ $log->path }}</div>
                                </td>
                                <td class="small">{{ $log->route_name ?? '—' }}</td>
                                <td class="small">{{ $log->ip_address ?? '—' }}</td>
                                <td style="min-width: 320px;">
                                    @if (!empty($log->query_params))
                                        <div class="small"><b>query:</b> {{ json_encode($log->query_params, JSON_UNESCAPED_UNICODE) }}</div>
                                    @endif
                                    @if (!empty($log->payload))
                                        <div class="small"><b>body:</b> {{ json_encode($log->payload, JSON_UNESCAPED_UNICODE) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Записей пока нет</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection

