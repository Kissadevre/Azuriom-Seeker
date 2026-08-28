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

        Schema::create('seeker_publication_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('publication_id');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('publication_id')->references('id')->on('seeker_publications')->cascadeOnDelete();
            $table->index(['publication_id', 'position']);
        });

        Schema::create('seeker_publication_media', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('publication_id');
            $table->string('type', 16);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();

            $table->foreign('publication_id')->references('id')->on('seeker_publications')->cascadeOnDelete();
            $table->unique(['publication_id', 'type']);
        });

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

        Schema::create('seeker_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conversation_id')->nullable();
            $table->unsignedInteger('publication_id');
            $table->unsignedInteger('reviewer_id');
            $table->unsignedInteger('reviewed_user_id');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('seeker_conversations')->cascadeOnDelete();
            $table->foreign('publication_id')->references('id')->on('seeker_publications')->cascadeOnDelete();
            $table->foreign('reviewer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['publication_id', 'reviewer_id', 'reviewed_user_id'], 'seeker_reviews_unique');
            $table->unique(['conversation_id', 'reviewer_id'], 'seeker_reviews_conversation_reviewer_unique');
            $table->index(['reviewed_user_id', 'is_visible']);
        });

        Schema::create('seeker_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique();
            $table->text('bio')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('seeker_profile_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('profile_user_id');
            $table->unsignedInteger('reporter_id');
            $table->string('reason', 32);
            $table->text('details');
            $table->text('reported_bio')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->foreign('profile_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('reporter_id')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['profile_user_id', 'reporter_id']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('seeker_publication_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('publication_id');
            $table->unsignedInteger('reporter_id');
            $table->string('reason', 32);
            $table->text('details');
            $table->string('reported_title', 120);
            $table->text('reported_description');
            $table->string('reported_portfolio_url', 2048)->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->foreign('publication_id')->references('id')->on('seeker_publications')->restrictOnDelete();
            $table->foreign('reporter_id')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['publication_id', 'reporter_id']);
            $table->index(['status', 'created_at']);
        });

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
        Schema::dropIfExists('seeker_reviews');
        Schema::dropIfExists('seeker_conversation_reports');
        Schema::dropIfExists('seeker_messages');
        Schema::dropIfExists('seeker_conversations');
        Schema::dropIfExists('seeker_publication_reports');
        Schema::dropIfExists('seeker_publication_media');
        Schema::dropIfExists('seeker_publication_images');
        Schema::dropIfExists('seeker_profile_reports');
        Schema::dropIfExists('seeker_profiles');
        Schema::dropIfExists('seeker_user_restrictions');
        Schema::dropIfExists('seeker_publications');
    }
};
