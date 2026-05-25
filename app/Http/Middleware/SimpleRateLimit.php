<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleRateLimit
{
    public function handle(Request $request, Closure $next, int $limit = 120, int $minutes = 1): Response
    {
        $ip = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $request->ip() ?: 'unknown');
        $file = storage_path("framework/cache/notes-rate-{$ip}.json");
        $now = time();
        $window = max(1, $minutes) * 60;
        $state = ['reset_at' => $now + $window, 'count' => 0];

        if (is_file($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded) && (int) ($decoded['reset_at'] ?? 0) > $now) {
                $state = $decoded;
            }
        }

        $state['count'] = (int) $state['count'] + 1;
        if ($state['count'] > $limit) {
            return response()->json(['message' => 'Rate limit exceeded'], 429);
        }

        file_put_contents($file, json_encode($state), LOCK_EX);

        return $next($request);
    }
}

