<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Services\EmbeddingService;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        $embeddings = new EmbeddingService();

        foreach ([
            [
                'title' => 'Project kickoff',
                'content' => 'Define scope for the notes API, frontend UI, semantic search, and summary workflow. Prioritize secure validation and clean JSON responses.',
                'tags' => 'planning,api,ai',
            ],
            [
                'title' => 'AI usage log',
                'content' => 'Document prompts, validation steps, and fallback behavior so reviewers can understand how AI-assisted development was used responsibly.',
                'tags' => 'documentation,ai',
            ],
        ] as $note) {
            $note['embedding'] = $embeddings->embed("{$note['title']} {$note['content']} {$note['tags']}");
            Note::query()->updateOrCreate(
                ['title' => $note['title']],
                $note
            );
        }
    }
}
