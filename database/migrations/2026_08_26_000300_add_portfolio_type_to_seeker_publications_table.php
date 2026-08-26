<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seeker_publications', function (Blueprint $table) {
            $table->string('portfolio_type', 32)->default('images');
        });

        DB::table('seeker_publications')
            ->whereNotNull('portfolio_url')
            ->update(['portfolio_type' => 'external']);
    }

    public function down(): void
    {
        Schema::table('seeker_publications', function (Blueprint $table) {
            $table->dropColumn('portfolio_type');
        });
    }
};
