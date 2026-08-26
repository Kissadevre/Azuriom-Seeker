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
            $table->string('price_basis', 32)->nullable();
        });

        DB::table('seeker_publications')
            ->where('pricing_type', 'points')
            ->update(['price_basis' => 'fixed']);
    }

    public function down(): void
    {
        Schema::table('seeker_publications', function (Blueprint $table) {
            $table->dropColumn('price_basis');
        });
    }
};
