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
            $table->string('completion_status', 32)->default('none');
            $table->unsignedInteger('delivery_attempts')->default(0);
            $table->string('escrow_status', 32)->default('none');
            $table->decimal('held_points', 14, 2)->default(0);
            $table->decimal('proposed_hours', 8, 2)->nullable();
            $table->decimal('service_points', 14, 2)->nullable();
            $table->decimal('tip_points', 14, 2)->default(0);
            $table->text('final_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('completion_requested_at')->nullable();
            $table->timestamp('completion_responded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('publication_id')->references('id')->on('seeker_publications')->restrictOnDelete();
            $table->foreign('client_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('author_id')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['publication_id', 'client_id']);
            $table->index(['client_id', 'status']);
            $table->index(['author_id', 'status']);
            $table->index('last_message_at');
            $table->index(
                ['completion_status', 'completion_requested_at'],
                'seeker_conversations_completion_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_conversations');
    }
};
