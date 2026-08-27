<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seeker_messages', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('content');
            $table->string('image_original_name')->nullable()->after('image_path');
            $table->string('image_mime_type', 100)->nullable()->after('image_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('seeker_messages', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'image_original_name', 'image_mime_type']);
        });
    }
};
