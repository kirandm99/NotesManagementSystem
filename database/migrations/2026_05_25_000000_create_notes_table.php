<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->text('content');
            $table->string('tags', 255)->nullable();
            $table->text('summary')->nullable();
            $table->json('embedding')->nullable();
            $table->timestamps();
            $table->index('updated_at');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE notes ADD FULLTEXT ft_notes_title_content_tags (title, content, tags)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};

