<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('sender_id');
            $table->text('content');
            $table->string('image_path')->nullable();
            $table->string('image_original_name')->nullable();
            $table->string('image_mime_type', 100)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('hidden_at')->nullable();
            $table->unsignedInteger('hidden_by_id')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('seeker_conversations')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['conversation_id', 'id']);
            $table->index(['conversation_id', 'read_at']);
            $table->index('hidden_at');
            $table->index('hidden_by_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_messages');
    }
};
