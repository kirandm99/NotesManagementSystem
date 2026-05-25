const baseUrl = process.env.API_BASE_URL || 'http://localhost:8080';

async function request(path, options = {}) {
    const response = await fetch(`${baseUrl}${path}`, {
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...(options.headers || {}) },
        ...options,
    });
    const payload = await response.json();
    if (!response.ok) {
        throw new Error(`${options.method || 'GET'} ${path} failed: ${response.status} ${JSON.stringify(payload)}`);
    }
    return payload;
}

async function main() {
    await request('/api/health');

    const created = await request('/api/notes', {
        method: 'POST',
        body: JSON.stringify({
            title: `Laravel smoke test ${Date.now()}`,
            content: 'This note validates create, list, search, summary, update, and delete endpoints.',
            tags: 'laravel,smoke,test',
        }),
    });

    const id = created.data.id;
    await request(`/api/notes/${id}`);
    await request('/api/notes?page=1&limit=10');
    await request('/api/notes/search?q=validate');
    await request(`/api/notes/${id}/summary`, { method: 'POST' });
    await request(`/api/notes/${id}`, {
        method: 'PUT',
        body: JSON.stringify({
            title: 'Updated Laravel smoke test',
            content: 'Updated content confirms the PUT endpoint remains healthy.',
            tags: 'laravel,updated',
        }),
    });
    await request(`/api/notes/${id}`, { method: 'DELETE' });

    console.log('Laravel smoke test passed');
}

main().catch((error) => {
    console.error(error.message);
    process.exit(1);
});

