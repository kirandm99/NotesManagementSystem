const state = {
    currentId: null,
    notes: [],
};

const els = {
    list: document.querySelector('#notesList'),
    form: document.querySelector('#noteForm'),
    title: document.querySelector('#noteTitle'),
    tags: document.querySelector('#noteTags'),
    content: document.querySelector('#noteContent'),
    summary: document.querySelector('#summaryText'),
    searchForm: document.querySelector('#searchForm'),
    searchInput: document.querySelector('#searchInput'),
    toast: document.querySelector('#toast'),
    newNote: document.querySelector('#newNote'),
    reload: document.querySelector('#reloadNotes'),
    summaryButton: document.querySelector('#summaryButton'),
    deleteButton: document.querySelector('#deleteButton'),
};

async function api(path, options = {}) {
    const response = await fetch(path, {
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...(options.headers || {}) },
        ...options,
    });
    const payload = await response.json();
    if (!response.ok) {
        const message = payload.message || payload.error || 'Request failed';
        throw new Error(message);
    }
    return payload;
}

function toast(message) {
    els.toast.textContent = message;
    els.toast.style.display = 'block';
    window.setTimeout(() => {
        els.toast.style.display = 'none';
    }, 2800);
}

function clearEditor() {
    state.currentId = null;
    els.form.reset();
    els.summary.textContent = 'Write a note, save it, then generate a summary.';
    renderList();
}

function selectNote(note) {
    state.currentId = note.id;
    els.title.value = note.title || '';
    els.tags.value = note.tags || '';
    els.content.value = note.content || '';
    els.summary.textContent = note.summary || 'No summary generated yet.';
    renderList();
}

function renderList() {
    if (state.notes.length === 0) {
        els.list.innerHTML = '<p class="note-meta">No notes found.</p>';
        return;
    }

    els.list.innerHTML = state.notes.map((note) => `
        <button class="note-item ${Number(note.id) === Number(state.currentId) ? 'active' : ''}" data-id="${note.id}">
            <span class="note-title">${escapeHtml(note.title)}</span>
            <span class="note-snippet">${escapeHtml((note.content || '').slice(0, 110))}</span>
            <span class="note-meta">${escapeHtml(note.tags || 'untagged')}</span>
        </button>
    `).join('');
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

async function loadNotes() {
    const payload = await api('/api/notes?page=1&limit=20');
    state.notes = payload.data || [];
    renderList();
}

async function loadInitialState() {
    const params = new URLSearchParams(window.location.search);
    const query = params.get('q') || '';
    const noteId = params.get('note');

    if (query.trim().length >= 2) {
        els.searchInput.value = query;
        const payload = await api(`/api/notes/search?q=${encodeURIComponent(query.trim())}`);
        state.notes = payload.data || [];
        renderList();
        return;
    }

    await loadNotes();

    if (noteId) {
        const payload = await api(`/api/notes/${encodeURIComponent(noteId)}`);
        selectNote(payload.data);
    }
}

els.list.addEventListener('click', (event) => {
    const item = event.target.closest('.note-item');
    if (!item) return;
    const note = state.notes.find((candidate) => Number(candidate.id) === Number(item.dataset.id));
    if (note) selectNote(note);
});

els.form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const body = JSON.stringify({
        title: els.title.value,
        tags: els.tags.value,
        content: els.content.value,
    });
    const path = state.currentId ? `/api/notes/${state.currentId}` : '/api/notes';
    const method = state.currentId ? 'PUT' : 'POST';
    const payload = await api(path, { method, body });
    state.currentId = payload.data.id;
    toast('Note saved');
    await loadNotes();
});

els.searchForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const query = els.searchInput.value.trim();
    if (query.length < 2) {
        await loadNotes();
        return;
    }
    const payload = await api(`/api/notes/search?q=${encodeURIComponent(query)}`);
    state.notes = payload.data || [];
    renderList();
});

els.summaryButton.addEventListener('click', async () => {
    if (!state.currentId) {
        toast('Save or select a note first');
        return;
    }
    const payload = await api(`/api/notes/${state.currentId}/summary`, { method: 'POST' });
    els.summary.textContent = payload.data.summary || 'No summary generated.';
    toast(`Summary generated with ${payload.ai.provider}`);
    await loadNotes();
});

els.deleteButton.addEventListener('click', async () => {
    if (!state.currentId) return;
    await api(`/api/notes/${state.currentId}`, { method: 'DELETE' });
    toast('Note deleted');
    clearEditor();
    await loadNotes();
});

els.newNote.addEventListener('click', clearEditor);
els.reload.addEventListener('click', loadNotes);

loadInitialState().catch((error) => toast(error.message));
