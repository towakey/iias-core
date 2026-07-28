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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->text('url');
            $table->string('title')->nullable();
            $table->string('domain')->nullable();
            $table->timestamp('visited_at');
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('source_data')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'visited_at']);
            $table->index(['domain', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
