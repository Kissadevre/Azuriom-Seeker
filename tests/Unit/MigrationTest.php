<?php

namespace Tests\Unit;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    /** @var array<string, string> */
    private const MIGRATIONS = [
        '2026_08_28_000000_create_seeker_publications_table.php' => 'seeker_publications',
        '2026_08_28_000100_create_seeker_publication_images_table.php' => 'seeker_publication_images',
        '2026_08_28_000200_create_seeker_publication_media_table.php' => 'seeker_publication_media',
        '2026_08_28_000300_create_seeker_conversations_table.php' => 'seeker_conversations',
        '2026_08_28_000400_create_seeker_messages_table.php' => 'seeker_messages',
        '2026_08_28_000500_create_seeker_conversation_reports_table.php' => 'seeker_conversation_reports',
        '2026_08_28_000600_create_seeker_reviews_table.php' => 'seeker_reviews',
        '2026_08_28_000700_create_seeker_profiles_table.php' => 'seeker_profiles',
        '2026_08_28_000800_create_seeker_profile_reports_table.php' => 'seeker_profile_reports',
        '2026_08_28_000900_create_seeker_publication_reports_table.php' => 'seeker_publication_reports',
        '2026_08_28_001000_create_seeker_user_restrictions_table.php' => 'seeker_user_restrictions',
        '2026_08_28_001100_create_seeker_transactions_table.php' => 'seeker_transactions',
    ];

    /** @var string[] */
    private const ALTERATION_MIGRATIONS = [
        '2026_08_28_001200_allow_multiple_seeker_publication_media.php',
    ];

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

    public function test_separate_migrations_create_and_drop_the_complete_schema(): void
    {
        $connection = config('database.default');
        $databaseKey = 'database.connections.'.$connection.'.database';
        $originalDatabase = config($databaseKey);
        $migrations = [];
        DB::purge($connection);
        config([$databaseKey => ':memory:']);

        try {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
            });

            foreach (self::MIGRATIONS as $file => $table) {
                $migration = require dirname(__DIR__, 2).'/database/migrations/'.$file;

                $this->assertInstanceOf(Migration::class, $migration);
                $this->assertFalse(Schema::hasTable($table), $table.' must have its own migration');

                $migration->up();
                $migrations[] = $migration;

                $this->assertTrue(Schema::hasTable($table), $file.' did not create '.$table);
            }

            foreach (self::ALTERATION_MIGRATIONS as $file) {
                $migration = require dirname(__DIR__, 2).'/database/migrations/'.$file;

                $this->assertInstanceOf(Migration::class, $migration);
                $migration->up();
                $migrations[] = $migration;
            }

            $this->assertSchemaMatchesExpectedStructure();

            foreach (array_reverse($migrations) as $migration) {
                $migration->down();
            }

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

    private function assertSchemaMatchesExpectedStructure(): void
    {
        foreach (self::TABLE_COLUMNS as $table => $columns) {
            $this->assertLessThanOrEqual(64, strlen($table), $table);
            $this->assertEqualsCanonicalizing($columns, Schema::getColumnListing($table), $table);

            foreach ($columns as $column) {
                $this->assertLessThanOrEqual(64, strlen($column), $table.'.'.$column);
            }

            foreach (Schema::getIndexes($table) as $index) {
                $this->assertLessThanOrEqual(
                    64,
                    strlen($index['name']),
                    $index['name'].' exceeds the MariaDB identifier limit.'
                );
            }

            foreach (Schema::getForeignKeys($table) as $foreignKey) {
                $defaultName = $table.'_'.implode('_', $foreignKey['columns']).'_foreign';

                $this->assertLessThanOrEqual(
                    64,
                    strlen($defaultName),
                    $defaultName.' exceeds the MariaDB identifier limit.'
                );
            }
        }

        $foreignKeyCount = collect(array_keys(self::TABLE_COLUMNS))
            ->sum(fn (string $table) => count(Schema::getForeignKeys($table)));
        $indexCount = collect(array_keys(self::TABLE_COLUMNS))
            ->sum(fn (string $table) => count(Schema::getIndexes($table)));
        $mediaTypeUnique = collect(Schema::getIndexes('seeker_publication_media'))
            ->contains(fn (array $index) => $index['unique']
                && $index['columns'] === ['publication_id', 'type']);

        $this->assertSame(26, $foreignKeyCount);
        $this->assertSame(42, $indexCount);
        $this->assertFalse($mediaTypeUnique, 'Publication media must allow multiple files of the same type.');
    }
}
