<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiClient
{
    /**
     * Send a chat completion request to OpenAI.
     *
     * @param array $messages OpenAI-formatted chat messages.
     * @param array $tools OpenAI tool/function definitions.
     * @param array|string $toolChoice 'auto', 'none', or a forced {"type":"function","function":{"name":...}}.
     * @return array The response `message` object (may contain `content` and/or `tool_calls`).
     */
    public function chat(array $messages, array $tools = [], array|string $toolChoice = 'auto'): array
    {
        $key = config('services.openai.key');
        if (!$key) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $payload = [
            'model' => config('services.openai.model'),
            'messages' => $messages,
            'temperature' => 0.4,
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = $toolChoice;
        }

        $response = Http::withToken($key)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI request failed: ' . $response->body());
        }

        $choice = $response->json('choices.0.message');

        if (!$choice) {
            throw new RuntimeException('OpenAI returned an unexpected response.');
        }

        return $choice;
    }
}
