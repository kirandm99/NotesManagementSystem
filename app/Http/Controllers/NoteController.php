<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\EmbeddingService;
use App\Services\SummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function __construct(private readonly EmbeddingService $embeddings)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $notes = Note::query()
            ->latest('updated_at')
            ->paginate((int) ($validated['limit'] ?? 10));

        return response()->json([
            'data' => $notes->items(),
            'pagination' => [
                'page' => $notes->currentPage(),
                'limit' => $notes->perPage(),
                'total' => $notes->total(),
                'pages' => $notes->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $note = Note::create($this->validatedNote($request));

        return response()->json(['data' => $note], 201);
    }

    public function show(Note $note): JsonResponse
    {
        return response()->json(['data' => $note]);
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        $note->update($this->validatedNote($request));

        return response()->json(['data' => $note->fresh()]);
    }

    public function destroy(Note $note): JsonResponse
    {
        $note->delete();

        return response()->json(['message' => 'Note deleted']);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $queryVector = $this->embeddings->embed($validated['q']);
        $results = Note::query()
            ->get()
            ->map(function (Note $note) use ($queryVector): array {
                $vector = is_array($note->embedding)
                    ? $note->embedding
                    : $this->embeddings->embed("{$note->title} {$note->content} {$note->tags}");

                return [
                    ...$note->makeHidden('embedding')->toArray(),
                    'score' => $this->embeddings->similarity($queryVector, $vector),
                ];
            })
            ->sortByDesc('score')
            ->take(10)
            ->values();

        return response()->json(['data' => $results]);
    }

    public function summary(Note $note, SummaryService $summaries): JsonResponse
    {
        $result = $summaries->summarize($note->title, $note->content);
        $note->update(['summary' => $result['summary']]);

        return response()->json([
            'data' => $note->fresh(),
            'ai' => ['provider' => $result['provider']],
        ]);
    }

    private function validatedNote(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'content' => ['required', 'string', 'max:20000'],
            'tags' => ['nullable', 'string', 'max:255'],
        ]);

        $data['tags'] = $data['tags'] ?? '';
        $data['embedding'] = $this->embeddings->embed("{$data['title']} {$data['content']} {$data['tags']}");

        return $data;
    }
}

