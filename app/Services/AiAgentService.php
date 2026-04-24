<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiAgentService
{
    public function health(): array
    {
        $endpoint = config('services.ai.endpoint', 'http://127.0.0.1:11434');
        $timeout = (int) config('services.ai.timeout', 120);

        try {
            $response = Http::timeout($timeout)->get(rtrim($endpoint, '/') . '/api/tags');
            if (!$response->ok()) {
                return [
                    'ok' => false,
                    'message' => 'HTTP ' . $response->status(),
                    'models' => [],
                ];
            }

            $models = collect($response->json('models', []))
                ->map(fn($m) => $m['name'] ?? null)
                ->filter()
                ->values()
                ->toArray();

            return [
                'ok' => true,
                'message' => 'Ollama доступен',
                'models' => $models,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Нет соединения с Ollama: ' . $e->getMessage(),
                'models' => [],
            ];
        }
    }

    public function summarizeSurveyRisks(array $context): string
    {
        $system = 'Ты аналитик анонимных студенческих опросов. Пиши кратко, структурировано, без выдуманных фактов.';

        $user = "Сделай аналитическую сводку по рискам в анкете.\n"
            . "Обязательные разделы:\n"
            . "1) Ключевые сигналы риска\n"
            . "2) Где нужно ручное внимание администратора\n"
            . "3) Краткий план действий (3-5 пунктов)\n\n"
            . "Данные анкеты (JSON):\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $this->chat($system, $user);
    }

    private function chat(string $system, string $user): string
    {
        $endpoint = config('services.ai.endpoint', 'http://127.0.0.1:11434');
        $model = config('services.ai.model', 'qwen2.5:7b');
        $timeout = (int) config('services.ai.timeout', 120);

        $response = Http::timeout($timeout)->post(rtrim($endpoint, '/') . '/api/chat', [
            'model' => $model,
            'stream' => false,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ]);

        if (!$response->ok()) {
            throw new \RuntimeException('Ошибка ответа от AI: HTTP ' . $response->status());
        }

        $json = $response->json();
        $content = $json['message']['content'] ?? null;
        if (!is_string($content) || trim($content) === '') {
            throw new \RuntimeException('AI вернул пустой ответ');
        }

        return trim($content);
    }
}
