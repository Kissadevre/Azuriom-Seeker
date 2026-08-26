<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seeker_publications', function (Blueprint $table) {
            $table->boolean('is_guest_visible')->default(true);
            $table->string('pricing_type', 32)->default('negotiable');
            $table->decimal('price', 14, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('seeker_publications', function (Blueprint $table) {
            $table->dropColumn(['is_guest_visible', 'pricing_type', 'price']);
        });
    }
};
