<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Анкета уже отправлена</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5" style="max-width: 700px;">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-2">✅ Анкета уже отправлена</h4>
                <div class="text-muted mb-3">
                    Спасибо! Мы уже получили ваш ответ.
                </div>

                <div class="alert alert-info mb-0">
                    Группа: <b>{{ $survey->group->name ?? '' }}</b><br>
                    Анкета: <b>{{ $survey->template->title ?? '' }}</b>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
