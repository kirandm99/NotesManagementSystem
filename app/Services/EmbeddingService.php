<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EmbeddingService
{
    private const DIMENSIONS = 64;

    public function embed(string $text): array
    {
        $apiKey = (string) config('services.openai.key');

        if ($apiKey !== '') {
            $embedding = $this->embedWithOpenAI($apiKey, $text);
            if ($embedding !== null) {
                return $embedding;
            }
        }

        return $this->localEmbedding($text);
    }

    public function similarity(array $left, array $right): float
    {
        $score = 0.0;
        $limit = min(count($left), count($right));

        for ($i = 0; $i < $limit; $i++) {
            $score += (float) $left[$i] * (float) $right[$i];
        }

        return round($score, 6);
    }

    private function embedWithOpenAI(string $apiKey, string $text): ?array
    {
        $response = Http::withToken($apiKey)
            ->timeout(15)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => config('services.openai.embedding_model', 'text-embedding-3-small'),
                'input' => $text,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $embedding = $response->json('data.0.embedding');

        return is_array($embedding) ? $embedding : null;
    }

    private function localEmbedding(string $text): array
    {
        $tokens = preg_split('/[^a-z0-9]+/i', strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $vector = array_fill(0, self::DIMENSIONS, 0.0);

        foreach ($tokens as $token) {
            $index = abs((int) crc32($token)) % self::DIMENSIONS;
            $vector[$index] += 1.0;
        }

        $length = sqrt(array_sum(array_map(fn (float $value): float => $value * $value, $vector)));
        if ($length === 0.0) {
            return $vector;
        }

        return array_map(fn (float $value): float => round($value / $length, 6), $vector);
    }
}
