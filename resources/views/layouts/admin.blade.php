<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="d-flex" style="min-height:100vh;">

        {{-- Sidebar --}}
        <div class="bg-dark text-white p-3" style="width: 260px;">
            <div class="mb-3">
                <div class="fw-bold">Анонимные анкеты для оценки преподователей</div>
                <div class="small text-white-50">Админ-панель</div>
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

                <a class="list-group-item list-group-item-action {{ request()->is('admin/reports*') ? 'active' : '' }}"
                    href="{{ route('admin.reports.teachers.index') }}">
                    📊 Отчёты
                </a>

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

            <div class="mt-4 small text-white-50">
                {{ auth()->user()->email ?? '' }}
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-grow-1">

            <nav class="navbar navbar-light bg-white border-bottom">
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
