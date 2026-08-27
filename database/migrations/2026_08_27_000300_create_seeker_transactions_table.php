<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conversation_id')->nullable();
            $table->unsignedInteger('payer_id')->nullable();
            $table->unsignedInteger('payee_id')->nullable();
            $table->string('payer_name');
            $table->string('payee_name');
            $table->string('publication_title');
            $table->string('type', 32);
            $table->string('status', 32);
            $table->decimal('amount', 14, 2);
            $table->timestamp('held_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('seeker_conversations')->nullOnDelete();
            $table->foreign('payer_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('payee_id')->references('id')->on('users')->nullOnDelete();
            $table->unique(['conversation_id', 'type']);
            $table->index(['status', 'completed_at']);
            $table->index(['payer_id', 'created_at']);
            $table->index(['payee_id', 'created_at']);
        });

        $this->backfillExistingTransactions();
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_transactions');
    }

    private function backfillExistingTransactions(): void
    {
        DB::table('seeker_conversations as conversations')
            ->join('seeker_publications as publications', 'publications.id', '=', 'conversations.publication_id')
            ->join('users as payer', 'payer.id', '=', 'conversations.client_id')
            ->join('users as payee', 'payee.id', '=', 'conversations.author_id')
            ->select([
                'conversations.*',
                'publications.title as publication_title',
                'publications.price_basis',
                'payer.name as payer_name',
                'payee.name as payee_name',
            ])
            ->orderBy('conversations.id')
            ->chunkById(100, function ($conversations) {
                $transactions = [];

                foreach ($conversations as $conversation) {
                    $serviceAmount = in_array($conversation->escrow_status, ['held', 'refunded'], true)
                        ? (float) $conversation->held_points
                        : (float) $conversation->service_points;

                    if ($serviceAmount > 0) {
                        $status = match ($conversation->escrow_status) {
                            'released' => 'completed',
                            'refunded' => 'refunded',
                            default => 'held',
                        };
                        $transactions[] = [
                            'conversation_id' => $conversation->id,
                            'payer_id' => $conversation->client_id,
                            'payee_id' => $conversation->author_id,
                            'payer_name' => $conversation->payer_name,
                            'payee_name' => $conversation->payee_name,
                            'publication_title' => $conversation->publication_title,
                            'type' => 'service',
                            'status' => $status,
                            'amount' => $serviceAmount,
                            'held_at' => $conversation->price_basis === 'fixed' ? $conversation->created_at : null,
                            'completed_at' => $status === 'completed' ? ($conversation->completed_at ?? $conversation->updated_at) : null,
                            'refunded_at' => $status === 'refunded' ? $conversation->updated_at : null,
                            'created_at' => $conversation->created_at,
                            'updated_at' => $conversation->updated_at,
                        ];
                    }

                    if ((float) $conversation->tip_points > 0 && $conversation->escrow_status === 'released') {
                        $transactions[] = [
                            'conversation_id' => $conversation->id,
                            'payer_id' => $conversation->client_id,
                            'payee_id' => $conversation->author_id,
                            'payer_name' => $conversation->payer_name,
                            'payee_name' => $conversation->payee_name,
                            'publication_title' => $conversation->publication_title,
                            'type' => 'tip',
                            'status' => 'completed',
                            'amount' => (float) $conversation->tip_points,
                            'held_at' => null,
                            'completed_at' => $conversation->completed_at ?? $conversation->updated_at,
                            'refunded_at' => null,
                            'created_at' => $conversation->completed_at ?? $conversation->updated_at,
                            'updated_at' => $conversation->updated_at,
                        ];
                    }
                }

                if ($transactions !== []) {
                    DB::table('seeker_transactions')->insert($transactions);
                }
            }, 'conversations.id', 'id');
    }
};
