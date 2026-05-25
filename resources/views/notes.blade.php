<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI Notes Manager</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <main class="shell">
        <section class="workspace">
            <aside class="sidebar" aria-label="Notes">
                <div class="brand">
                    <span class="mark">N</span>
                    <div>
                        <h1>AI Notes</h1>
                        <p>Laravel API with semantic search</p>
                    </div>
                </div>

                <form class="search" id="searchForm">
                    <input id="searchInput" name="q" placeholder="Semantic search" autocomplete="off">
                    <button type="submit">Search</button>
                </form>

                <div class="toolbar">
                    <button id="newNote" type="button">New</button>
                    <button id="reloadNotes" type="button">Reload</button>
                </div>

                <div id="notesList" class="notes-list" aria-live="polite"></div>
            </aside>

            <section class="editor" aria-label="Note editor">
                <form id="noteForm">
                    <input id="noteTitle" name="title" maxlength="180" placeholder="Title" required>
                    <input id="noteTags" name="tags" maxlength="255" placeholder="Tags, comma separated">
                    <textarea id="noteContent" name="content" placeholder="Write your note..." required></textarea>

                    <div class="actions">
                        <button type="submit" class="primary">Save</button>
                        <button type="button" id="summaryButton">Summarize</button>
                        <button type="button" id="deleteButton" class="danger">Delete</button>
                    </div>
                </form>

                <section class="summary-panel">
                    <h2>Summary</h2>
                    <p id="summaryText">Select a note or create one to generate an AI summary.</p>
                </section>
            </section>
        </section>
        <div id="toast" role="status" aria-live="polite"></div>
    </main>

    <script src="/assets/app.js"></script>
</body>
</html>

