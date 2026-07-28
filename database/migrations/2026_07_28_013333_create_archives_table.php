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
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('archive_type')->comment('history, memo, image, shopping, note');
            $table->string('title')->nullable();
            $table->text('url')->nullable();
            $table->longText('body')->nullable()->comment('page body or memo');
            $table->text('memo')->nullable();
            $table->string('image_path')->nullable();
            $table->json('source_data')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('visited_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'archive_type', 'recorded_at']);
            $table->index(['user_id', 'visited_at']);
            $table->index(['url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
