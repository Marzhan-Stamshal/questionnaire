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

    public function summarizeSurveyRisks(array $context): array
    {
        $system = 'Ты аналитик анонимных студенческих опросов. Отвечай только по данным из контекста. Не выдумывай и не добавляй несуществующие вопросы.';

        $user = "Сформируй JSON-ответ для админки.\n"
            . "Формат JSON строго:\n"
            . "{\n"
            . "  \"key_signals\": [\"...\"],\n"
            . "  \"manual_attention\": [\"...\"],\n"
            . "  \"action_plan\": [\"...\"],\n"
            . "  \"question_references\": [{\"question_code\":\"...\",\"question_text\":\"...\",\"why\":\"...\"}]\n"
            . "}\n"
            . "Ограничения:\n"
            . "- Пиши только на русском языке.\n"
            . "- В пунктах не более 18 слов.\n"
            . "- В question_references указывай только вопросы из входного JSON.\n\n"
            . "Входные данные (JSON):\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $raw = $this->chat($system, $user, true);
        $parsed = json_decode($raw, true);
        if (!is_array($parsed)) {
            throw new \RuntimeException('AI вернул невалидный JSON');
        }

        return [
            'key_signals' => array_values(array_filter((array) ($parsed['key_signals'] ?? []))),
            'manual_attention' => array_values(array_filter((array) ($parsed['manual_attention'] ?? []))),
            'action_plan' => array_values(array_filter((array) ($parsed['action_plan'] ?? []))),
            'question_references' => array_values(array_filter((array) ($parsed['question_references'] ?? []), fn($x) => is_array($x))),
            'raw' => $raw,
        ];
    }

    private function chat(string $system, string $user, bool $jsonFormat = false): string
    {
        $endpoint = config('services.ai.endpoint', 'http://127.0.0.1:11434');
        $model = config('services.ai.model', 'qwen2.5:7b');
        $timeout = (int) config('services.ai.timeout', 120);

        $payload = [
            'model' => $model,
            'stream' => false,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'options' => [
                'temperature' => 0.2,
                'num_predict' => 280,
                'num_ctx' => 2048,
            ],
        ];

        if ($jsonFormat) {
            $payload['format'] = 'json';
        }

        $response = Http::timeout($timeout)->post(rtrim($endpoint, '/') . '/api/chat', $payload);

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
