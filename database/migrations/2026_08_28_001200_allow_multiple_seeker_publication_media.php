<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'seeker_publication_media';

    private const UNIQUE_INDEX = 'seeker_publication_media_publication_id_type_unique';

    public function up(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropUnique(self::UNIQUE_INDEX);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->unique(['publication_id', 'type'], self::UNIQUE_INDEX);
        });
    }
};
