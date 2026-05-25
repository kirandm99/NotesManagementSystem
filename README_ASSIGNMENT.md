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

Run the smoke test in another terminal:

```bash
node tests/smoke-api.js
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

## Architecture

- `app/Http/Controllers/NoteController.php`: Laravel API controller and validation.
- `app/Models/Note.php`: Eloquent note model.
- `database/migrations/2026_05_25_000000_create_notes_table.php`: notes schema.
- `app/Services/EmbeddingService.php`: local embeddings and cosine similarity.
- `app/Services/SummaryService.php`: OpenAI summary integration with local fallback.
- `resources/views/notes.blade.php`: frontend UI.
- `docs/openapi.yaml`: OpenAPI documentation.

## AI Usage

Codex was used to scaffold and adapt the assignment implementation into Laravel, generate the frontend UI, write API documentation, and create validation/smoke-test coverage. Generated code was checked against the assignment requirements and wired through Laravel validation, Eloquent models, migrations, route model binding, and Docker-based execution.

## Prompts Used

- "Build a Laravel notes management API with CRUD, validation, pagination, semantic search, and summary endpoint."
- "Create a simple frontend UI for managing notes, semantic search, and AI summaries."
- "Document setup, API routes, schema, architecture, AI usage, and validation steps."

## Security

- Laravel validation guards request payloads and query parameters.
- Eloquent parameter binding prevents SQL injection.
- API routes use Laravel rate limiting via `throttle:api`.
- OpenAI API keys are read from environment variables.
- Summary generation falls back locally when no API key is configured.

