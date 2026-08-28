<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_publication_media');
    }
};
