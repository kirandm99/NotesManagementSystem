<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SummaryService
{
    public function summarize(string $title, string $content): array
    {
        $apiKey = (string) config('services.openai.key');

        if ($apiKey !== '') {
            $summary = $this->summarizeWithOpenAI($apiKey, $title, $content);
            if ($summary !== null) {
                return ['summary' => $summary, 'provider' => 'openai'];
            }
        }

        return ['summary' => $this->localSummary($content), 'provider' => 'local'];
    }

    private function summarizeWithOpenAI(string $apiKey, string $title, string $content): ?string
    {
        $response = Http::withToken($apiKey)
            ->timeout(15)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4.1-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => 'Summarize notes in 2 concise bullet points.'],
                    ['role' => 'user', 'content' => "Title: {$title}\n\nContent:\n{$content}"],
                ],
                'temperature' => 0.2,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $summary = $response->json('choices.0.message.content');

        return is_string($summary) ? trim($summary) : null;
    }

    private function localSummary(string $content): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($content), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $selected = array_slice($sentences, 0, 2);

        return $selected === []
            ? 'No content available to summarize.'
            : implode(' ', $selected);
    }
}

