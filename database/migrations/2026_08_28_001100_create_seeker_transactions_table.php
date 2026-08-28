<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conversation_id')->nullable();
            $table->unsignedInteger('payer_id')->nullable();
            $table->unsignedInteger('payee_id')->nullable();
            $table->string('payer_name');
            $table->string('payee_name');
            $table->string('publication_title');
            $table->string('type', 32);
            $table->string('status', 32);
            $table->decimal('amount', 14, 2);
            $table->timestamp('held_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('seeker_conversations')->nullOnDelete();
            $table->foreign('payer_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('payee_id')->references('id')->on('users')->nullOnDelete();
            $table->unique(['conversation_id', 'type']);
            $table->index(['status', 'completed_at']);
            $table->index(['payer_id', 'created_at']);
            $table->index(['payee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_transactions');
    }
};
