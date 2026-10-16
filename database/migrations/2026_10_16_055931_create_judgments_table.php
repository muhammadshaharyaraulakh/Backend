<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('judgments', function (Blueprint $table) {
        $table->id();
        $table->string('citation')->unique();
        $table->string('title');
        $table->unsignedSmallInteger('year');
        $table->string('subject')->nullable();
        $table->json('judges')->nullable();
        $table->longText('full_text');
        $table->text('summary')->nullable();
        $table->string('source_url')->nullable();
        $table->timestamp('ingested_at')->nullable();
        $table->foreignId('court_id')->constrained()->cascadeOnDelete();
        $table->timestamps();
        });
        // RAW SQL IN pgvector and tsvector 
        DB::statement('ALTER TABLE judgments ADD COLUMN embedding vector(1536)');
        DB::statement('ALTER TABLE judgments ADD COLUMN search_vector tsvector');
        DB::statement('CREATE INDEX judgments_embedding_idx ON judgments USING ivfflat (embedding vector_cosine_ops)');
        DB::statement('CREATE INDEX judgments_search_vector_idx ON judgments USING gin (search_vector)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('judgments');
    }
};
