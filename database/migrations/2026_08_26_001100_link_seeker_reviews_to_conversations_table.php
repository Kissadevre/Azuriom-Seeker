<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seeker_reviews', function (Blueprint $table) {
            $table->unsignedInteger('conversation_id')->nullable()->after('id');

            $table->foreign('conversation_id')->references('id')->on('seeker_conversations')->cascadeOnDelete();
            $table->unique(['conversation_id', 'reviewer_id'], 'seeker_reviews_conversation_reviewer_unique');
        });
    }

    public function down(): void
    {
        Schema::table('seeker_reviews', function (Blueprint $table) {
            $table->dropUnique('seeker_reviews_conversation_reviewer_unique');
            $table->dropForeign(['conversation_id']);
            $table->dropColumn('conversation_id');
        });
    }
};
