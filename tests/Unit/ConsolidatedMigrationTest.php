<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsolidatedMigrationTest extends TestCase
{
    /** @var array<string, array<int, string>> */
    private const TABLE_COLUMNS = [
        'seeker_publications' => ['id', 'user_id', 'type', 'title', 'description', 'portfolio_type', 'portfolio_url', 'is_guest_visible', 'pricing_type', 'price', 'price_basis', 'status', 'is_pinned', 'pinned_at', 'published_at', 'created_at', 'updated_at', 'deleted_at'],
        'seeker_publication_images' => ['id', 'publication_id', 'path', 'original_name', 'mime_type', 'position', 'created_at', 'updated_at'],
        'seeker_publication_media' => ['id', 'publication_id', 'type', 'path', 'original_name', 'mime_type', 'size', 'created_at', 'updated_at'],
        'seeker_conversations' => ['id', 'publication_id', 'client_id', 'author_id', 'status', 'completion_status', 'delivery_attempts', 'escrow_status', 'held_points', 'proposed_hours', 'service_points', 'tip_points', 'final_message', 'last_message_at', 'completion_requested_at', 'completion_responded_at', 'completed_at', 'created_at', 'updated_at'],
        'seeker_messages' => ['id', 'conversation_id', 'sender_id', 'content', 'image_path', 'image_original_name', 'image_mime_type', 'read_at', 'hidden_at', 'hidden_by_id', 'created_at', 'updated_at'],
        'seeker_conversation_reports' => ['id', 'conversation_id', 'reporter_id', 'reported_user_id', 'reported_through_message_id', 'reason', 'details', 'status', 'created_at', 'updated_at'],
        'seeker_reviews' => ['id', 'conversation_id', 'publication_id', 'reviewer_id', 'reviewed_user_id', 'rating', 'comment', 'is_visible', 'created_at', 'updated_at'],
        'seeker_profiles' => ['id', 'user_id', 'bio', 'created_at', 'updated_at'],
        'seeker_profile_reports' => ['id', 'profile_user_id', 'reporter_id', 'reason', 'details', 'reported_bio', 'status', 'created_at', 'updated_at'],
        'seeker_publication_reports' => ['id', 'publication_id', 'reporter_id', 'reason', 'details', 'reported_title', 'reported_description', 'reported_portfolio_url', 'status', 'created_at', 'updated_at'],
        'seeker_user_restrictions' => ['id', 'user_id', 'created_by_id', 'revoked_by_id', 'type', 'reason', 'expires_at', 'revoked_at', 'created_at', 'updated_at'],
        'seeker_transactions' => ['id', 'conversation_id', 'payer_id', 'payee_id', 'payer_name', 'payee_name', 'publication_title', 'type', 'status', 'amount', 'held_at', 'completed_at', 'refunded_at', 'created_at', 'updated_at'],
    ];

    public function test_consolidated_migration_creates_and_drops_the_complete_schema(): void
    {
        $connection = config('database.default');
        $databaseKey = 'database.connections.'.$connection.'.database';
        $originalDatabase = config($databaseKey);
        DB::purge($connection);
        config([$databaseKey => ':memory:']);

        try {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
            });

            $migration = require dirname(__DIR__, 2).'/database/migrations/2026_08_26_000000_create_seeker_publications_table.php';
            $migration->up();

            foreach (self::TABLE_COLUMNS as $table => $columns) {
                $this->assertTrue(Schema::hasTable($table), $table);
                $this->assertEqualsCanonicalizing($columns, Schema::getColumnListing($table), $table);
            }

            $foreignKeyCount = collect(array_keys(self::TABLE_COLUMNS))
                ->sum(fn (string $table) => count(Schema::getForeignKeys($table)));
            $indexCount = collect(array_keys(self::TABLE_COLUMNS))
                ->sum(fn (string $table) => count(Schema::getIndexes($table)));

            $this->assertSame(26, $foreignKeyCount);
            $this->assertSame(43, $indexCount);

            $migration->down();

            foreach (array_keys(self::TABLE_COLUMNS) as $table) {
                $this->assertFalse(Schema::hasTable($table), $table);
            }
        } finally {
            foreach (array_reverse(array_keys(self::TABLE_COLUMNS)) as $table) {
                Schema::dropIfExists($table);
            }

            Schema::dropIfExists('users');
            DB::purge($connection);
            config([$databaseKey => $originalDatabase]);
        }
    }
}
