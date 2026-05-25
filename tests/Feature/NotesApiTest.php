<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }

    public function test_note_crud_pagination_search_and_summary_flow(): void
    {
        $created = $this->postJson('/api/notes', [
            'title' => 'Release planning',
            'content' => 'Prepare milestones, owners, validation steps, and deployment notes.',
            'tags' => 'release,planning',
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Release planning')
            ->json('data');

        $id = $created['id'];

        $this->getJson("/api/notes/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id);

        $this->getJson('/api/notes?page=1&limit=10')
            ->assertOk()
            ->assertJsonPath('pagination.page', 1)
            ->assertJsonPath('pagination.limit', 10)
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/notes/search?q=deployment')
            ->assertOk()
            ->assertJsonPath('data.0.id', $id)
            ->assertJsonStructure(['data' => [['id', 'title', 'content', 'tags', 'score']]]);

        $this->postJson("/api/notes/{$id}/summary")
            ->assertOk()
            ->assertJsonPath('ai.provider', 'local')
            ->assertJsonStructure(['data' => ['summary']]);

        $this->putJson("/api/notes/{$id}", [
            'title' => 'Updated release planning',
            'content' => 'Updated validation checklist and launch notes.',
            'tags' => 'release,updated',
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated release planning');

        $this->deleteJson("/api/notes/{$id}")
            ->assertOk()
            ->assertJsonPath('message', 'Note deleted');

        $this->getJson("/api/notes/{$id}")
            ->assertNotFound();
    }

    public function test_create_note_validates_required_fields(): void
    {
        $this->postJson('/api/notes', [
            'title' => '',
            'content' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content']);
    }

    public function test_frontend_page_loads(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('AI Notes');
    }
}

