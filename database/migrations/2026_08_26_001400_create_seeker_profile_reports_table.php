<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_profile_reports');
    }
};
