<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Выбор цикла — Анкета</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5" style="max-width: 700px;">

        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-2">Преподаватель глазами студентов</h4>
                <div class="text-muted mb-4">Выберите свой цикл, чтобы открыть анкету.</div>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('public.survey.goto') }}">
                    @csrf

                    <label class="form-label">Цикл *</label>
                    <select name="group_id" class="form-select mb-3" required>
                        <option value="">-- выберите цикл --</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>

                    <button class="btn btn-primary w-100 btn-lg">Открыть анкету</button>
                </form>
            </div>
        </div>

    </div>
</body>

</html>
