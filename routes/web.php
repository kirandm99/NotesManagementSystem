<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('notes');
});

Route::get('/docs', function () {
    return view('docs');
});

Route::get('/openapi.yaml', function () {
    return response()->file(base_path('docs/openapi.yaml'), [
        'Content-Type' => 'application/yaml',
    ]);
});
