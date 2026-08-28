<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_publications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('type', 32);
            $table->string('title', 120);
            $table->text('description');
            $table->string('portfolio_type', 32)->default('images');
            $table->string('portfolio_url', 2048)->nullable();
            $table->boolean('is_guest_visible')->default(true);
            $table->string('pricing_type', 32)->default('negotiable');
            $table->decimal('price', 14, 2)->nullable();
            $table->string('price_basis', 32)->nullable();
            $table->string('status', 32)->default('active');
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('pinned_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['status', 'published_at']);
            $table->index(['type', 'status']);
            $table->index(['is_pinned', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_publications');
    }
};
