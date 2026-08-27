<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seeker_conversations', function (Blueprint $table) {
            $table->string('completion_status', 32)->default('none')->after('status');
            $table->decimal('proposed_hours', 8, 2)->nullable()->after('held_points');
            $table->decimal('service_points', 14, 2)->nullable()->after('proposed_hours');
            $table->decimal('tip_points', 14, 2)->default(0)->after('service_points');
            $table->text('final_message')->nullable()->after('tip_points');
            $table->timestamp('completion_requested_at')->nullable()->after('last_message_at');
            $table->timestamp('completion_responded_at')->nullable()->after('completion_requested_at');
            $table->timestamp('completed_at')->nullable()->after('completion_responded_at');

            $table->index(['completion_status', 'completion_requested_at']);
        });
    }

    public function down(): void
    {
        Schema::table('seeker_conversations', function (Blueprint $table) {
            $table->dropIndex(['completion_status', 'completion_requested_at']);
            $table->dropColumn([
                'completion_status',
                'proposed_hours',
                'service_points',
                'tip_points',
                'final_message',
                'completion_requested_at',
                'completion_responded_at',
                'completed_at',
            ]);
        });
    }
};
