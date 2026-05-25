# Laravel AI Notes Management System

Laravel + MySQL notes app with CRUD APIs, pagination, semantic search, AI summaries, frontend UI, Docker, OpenAPI docs, and a smoke test.

## Run

```bash
docker compose up --build
```

Open:

```text
http://localhost:8080
```

Open API docs:

```text
http://localhost:8080/docs
```

Run the smoke test in another terminal:

```bash
node tests/smoke-api.js
```

Run PHPUnit feature tests:

```bash
docker compose exec app php artisan test
```

## API

- `GET /api/health`
- `GET /api/notes?page=1&limit=10`
- `POST /api/notes`
- `GET /api/notes/{id}`
- `PUT /api/notes/{id}`
- `DELETE /api/notes/{id}`
- `GET /api/notes/search?q=release`
- `POST /api/notes/{id}/summary`

## Database Schema

The `notes` table contains:

- `id`
- `title`
- `content`
- `tags`
- `summary`
- `embedding`
- `created_at`
- `updated_at`

The migration is in `database/migrations/2026_05_25_000000_create_notes_table.php`.

## Architecture

- `app/Http/Controllers/NoteController.php`: Laravel API controller and validation.
- `app/Models/Note.php`: Eloquent model.
- `database/migrations/2026_05_25_000000_create_notes_table.php`: notes schema.
- `app/Services/EmbeddingService.php`: OpenAI embeddings with local fallback and cosine similarity.
- `app/Services/SummaryService.php`: OpenAI summary integration with local fallback.
- `resources/views/notes.blade.php`: frontend UI.
- `resources/views/docs.blade.php`: Swagger UI for the OpenAPI file.
- `docs/openapi.yaml`: OpenAPI documentation.

## AI Usage

Codex was used to scaffold and adapt the assignment implementation into Laravel, generate the frontend UI, write API documentation, and create validation/smoke-test coverage. Generated code was checked against the assignment requirements and wired through Laravel validation, Eloquent models, migrations, route model binding, and Docker-based execution.

## Prompts Used

- "Build a Laravel notes management API with CRUD, validation, pagination, semantic search, and summary endpoint."
- "Create a simple frontend UI for managing notes, semantic search, and AI summaries."
- "Document setup, API routes, schema, architecture, AI usage, and validation steps."

## Security

- Laravel validation guards request payloads and query parameters.
- Eloquent and query builder parameter binding prevent SQL injection.
- API routes use a simple file-backed rate limiter.
- OpenAI API keys are read from environment variables.
- Summary generation falls back locally when no API key is configured.

## AI Summary Configuration

The app works without an external AI key by using a local extractive summary fallback. To use OpenAI summaries, set:

```bash
OPENAI_API_KEY=your_key_here
OPENAI_MODEL=gpt-4.1-mini
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
```

Then restart Docker:

```bash
docker compose up --build
```

## Submission Checklist

- Source code: submit this `laravel-notes` folder or push it to GitHub.
- Setup instructions: included above.
- API docs: `docs/openapi.yaml` and `http://localhost:8080/docs`.
- Database schema: Laravel migration in `database/migrations`.
- AI usage and prompts: included above.
- Validation evidence: `node tests/smoke-api.js` and `php artisan test`.
- Screenshots/video: add screenshots to `docs/screenshots`.
