<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('publication_id');
            $table->unsignedInteger('reviewer_id');
            $table->unsignedInteger('reviewed_user_id');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->foreign('publication_id')->references('id')->on('seeker_publications')->cascadeOnDelete();
            $table->foreign('reviewer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['publication_id', 'reviewer_id', 'reviewed_user_id'], 'seeker_reviews_unique');
            $table->index(['reviewed_user_id', 'is_visible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_reviews');
    }
};
