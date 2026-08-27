<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seeker_conversations', function (Blueprint $table) {
            $table->unsignedInteger('delivery_attempts')->default(0)->after('completion_status');
        });

        DB::table('seeker_conversations')
            ->where('completion_status', '!=', 'none')
            ->update(['delivery_attempts' => 1]);
    }

    public function down(): void
    {
        Schema::table('seeker_conversations', function (Blueprint $table) {
            $table->dropColumn('delivery_attempts');
        });
    }
};
