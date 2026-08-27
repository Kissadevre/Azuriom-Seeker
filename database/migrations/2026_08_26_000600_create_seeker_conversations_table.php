<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('publication_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('author_id');
            $table->string('status', 32)->default('active');
            $table->string('escrow_status', 32)->default('none');
            $table->decimal('held_points', 14, 2)->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('publication_id')->references('id')->on('seeker_publications')->restrictOnDelete();
            $table->foreign('client_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('author_id')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['publication_id', 'client_id']);
            $table->index(['client_id', 'status']);
            $table->index(['author_id', 'status']);
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_conversations');
    }
};
