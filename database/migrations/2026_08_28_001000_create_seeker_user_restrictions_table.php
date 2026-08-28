<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_user_restrictions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('created_by_id');
            $table->unsignedInteger('revoked_by_id')->nullable();
            $table->string('type', 32);
            $table->text('reason');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('revoked_by_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['user_id', 'type', 'revoked_at', 'expires_at'], 'seeker_restrictions_active_index');
            $table->index(['created_at', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_user_restrictions');
    }
};
