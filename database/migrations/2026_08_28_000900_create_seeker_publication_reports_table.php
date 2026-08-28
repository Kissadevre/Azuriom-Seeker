<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_publication_reports');
    }
};
