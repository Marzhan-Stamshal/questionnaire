<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-app: #f4f6fb;
            --bg-sidebar: linear-gradient(180deg, #111827 0%, #1e293b 100%);
            --text-soft: #94a3b8;
            --card-radius: 14px;
        }

        body {
            background: radial-gradient(circle at 10% 0%, #eef3ff 0%, #f4f6fb 45%, #f8f9fc 100%);
        }

        .admin-sidebar {
            width: 272px;
            background: var(--bg-sidebar);
            box-shadow: 6px 0 24px rgba(2, 6, 23, 0.18);
        }

        .admin-sidebar .brand-title {
            font-weight: 700;
            letter-spacing: .2px;
        }

        .admin-sidebar .brand-subtitle {
            color: var(--text-soft);
        }

        .admin-sidebar .list-group-item {
            border: 0;
            border-radius: 10px;
            margin-bottom: 6px;
            background: transparent;
            color: #e2e8f0;
            transition: all .16s ease;
        }

        .admin-sidebar .list-group-item:hover {
            background: rgba(148, 163, 184, 0.18);
            color: #fff;
        }

        .admin-sidebar .list-group-item.active {
            background: linear-gradient(90deg, #2563eb, #0ea5e9);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.34);
        }

        .admin-topbar {
            border-bottom: 1px solid #e5e7eb;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(6px);
        }

        .card {
            border-radius: var(--card-radius);
            border: 1px solid #e8edf5;
        }

        .btn {
            border-radius: 10px;
        }

        .table> :not(caption)>*>* {
            border-bottom-color: #eef2f7;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .page-title {
            margin: 0;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .page-subtitle {
            color: #64748b;
            font-size: .94rem;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: .82rem;
            color: #334155;
            margin-right: 6px;
            margin-top: 6px;
        }

        .soft-card {
            border-radius: 14px;
            border: 1px solid #e6ecf5;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .sticky-filters {
            position: sticky;
            top: 8px;
            z-index: 10;
        }

        .table-clean thead th {
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
            border-bottom: 1px solid #e7edf6;
        }

        .table-clean tbody tr:hover {
            background: #f8fbff;
        }

        .pagination {
            margin-bottom: 0;
            gap: 4px;
        }

        .pagination .page-link {
            padding: .32rem .58rem;
            font-size: .86rem;
            border-radius: 8px;
        }

        .pagination .page-item.active .page-link {
            border-color: #2563eb;
            background: #2563eb;
        }
    </style>
</head>

<body class="bg-light">

    <div class="d-flex" style="min-height:100vh;">

        {{-- Sidebar --}}
        <div class="admin-sidebar text-white p-3">
            <div class="mb-3">
                <div class="brand-title">Анонимные анкеты для оценки преподавателей</div>
                <div class="small brand-subtitle">Админ-панель</div>
            </div>

            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action {{ request()->is('admin/dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    📌 Главная
                </a>

                <a class="list-group-item list-group-item-action {{ request()->is('admin/surveys*') ? 'active' : '' }}"
                    href="{{ route('admin.surveys.index') }}">
                    🧾 Анкеты
                </a>

                <a class="list-group-item list-group-item-action {{ request()->is('admin/reports/teachers*') ? 'active' : '' }}"
                    href="{{ route('admin.reports.teachers.index') }}">
                    📊 Отчёты
                </a>
                <a class="list-group-item list-group-item-action {{ request()->is('admin/reports/analytics*') ? 'active' : '' }}"
                    href="{{ route('admin.reports.analytics.index') }}">
                    📈 Единая аналитика
                </a>
                @if (auth()->check() && auth()->user()->can_view_sensitive_reports)
                    <a class="list-group-item list-group-item-action {{ request()->is('admin/audit/logs*') ? 'active' : '' }}"
                        href="{{ route('admin.audit.logs.index') }}">
                        🛡️ Журнал аудита
                    </a>
                @endif

                <a class="list-group-item list-group-item-action {{ request()->is('admin/templates*') ? 'active' : '' }}"
                    href="{{ route('admin.templates.index') }}">
                    🧩 Шаблоны
                </a>

                <a class="list-group-item list-group-item-action {{ request()->is('admin/import*') ? 'active' : '' }}"
                    href="{{ route('admin.import.index') }}">
                    📥 Импорт
                </a>

                <a class="list-group-item list-group-item-action {{ request()->is('admin/groups*') ? 'active' : '' }}"
                    href="{{ route('admin.groups.index') }}">
                    👥 Группы
                </a>

                <a class="list-group-item list-group-item-action {{ request()->is('admin/teachers*') ? 'active' : '' }}"
                    href="{{ route('admin.teachers.index') }}">
                    👨‍🏫 Преподаватели
                </a>
                <a class="list-group-item list-group-item-action {{ request()->is('admin/assignments*') ? 'active' : '' }}"
                    href="{{ route('admin.assignments.index') }}">
                    🔗 Связи (Группа ↔ Преподаватель)
                </a>
            </div>

            <div class="mt-4 small brand-subtitle">
                {{ auth()->user()->email ?? '' }}
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-grow-1">

            <nav class="navbar navbar-light admin-topbar">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">@yield('header', 'Admin')</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm">Выйти</button>
                    </form>
                </div>
            </nav>

            <main class="p-4">
                @yield('content')
            </main>

        </div>
    </div>

</body>

</html>
