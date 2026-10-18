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
        Schema::create('ingestion_logs', function (Blueprint $table) {
        $table->id();
        $table->string('status')->default('running');
        $table->integer('fetched')->default(0);
        $table->integer('inserted')->default(0);
        $table->integer('skipped')->default(0);
        $table->integer('failed')->default(0);
        $table->text('error_message')->nullable();
        $table->timestamp('started_at');
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingestion_logs');
    }
};
