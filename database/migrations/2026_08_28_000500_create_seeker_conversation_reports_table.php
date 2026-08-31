<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_conversation_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('reporter_id');
            $table->unsignedInteger('reported_user_id');
            $table->unsignedInteger('reported_through_message_id')->nullable();
            $table->string('reason', 32);
            $table->text('details');
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('seeker_conversations')->cascadeOnDelete();
            $table->foreign('reporter_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('reported_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['conversation_id', 'reporter_id']);
            $table->index(['status', 'created_at']);
            $table->index('reported_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_conversation_reports');
    }
};
