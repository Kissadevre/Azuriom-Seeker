<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seeker_messages', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable()->after('read_at');
            $table->unsignedInteger('hidden_by_id')->nullable()->after('hidden_at');

            $table->index('hidden_at');
            $table->index('hidden_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('seeker_messages', function (Blueprint $table) {
            $table->dropIndex(['hidden_at']);
            $table->dropIndex(['hidden_by_id']);
            $table->dropColumn(['hidden_at', 'hidden_by_id']);
        });
    }
};
