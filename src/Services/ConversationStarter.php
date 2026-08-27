<?php

namespace Azuriom\Plugin\Seeker\Services;

use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConversationStarter
{
    /**
     * @return array{conversation: Conversation, created: bool}
     */
    public function start(Publication $publication, User $client, string $content): array
    {
        return DB::transaction(function () use ($publication, $client, $content) {
            $lockedPublication = Publication::query()
                ->lockForUpdate()
                ->findOrFail($publication->id);
            $lockedClient = User::query()
                ->lockForUpdate()
                ->findOrFail($client->id);

            abort_if($lockedPublication->user_id === $lockedClient->id, 403);
            abort_unless(
                $lockedPublication->status === Publication::STATUS_ACTIVE
                && $lockedPublication->published_at !== null
                && $lockedPublication->published_at->isPast(),
                404
            );

            $existing = Conversation::query()
                ->where('publication_id', $lockedPublication->id)
                ->where('client_id', $lockedClient->id)
                ->first();

            if ($existing !== null) {
                return ['conversation' => $existing, 'created' => false];
            }

            $heldPoints = 0.0;
            $escrowStatus = Conversation::ESCROW_NONE;

            if ($lockedPublication->requiresPointHold()) {
                $heldPoints = (float) $lockedPublication->price;

                if ($lockedClient->money < $heldPoints) {
                    throw ValidationException::withMessages([
                        'contact' => trans('seeker::messages.contact.insufficient_points', [
                            'price' => format_money($heldPoints),
                        ]),
                    ]);
                }

                $lockedClient->removeMoney($heldPoints);
                $escrowStatus = Conversation::ESCROW_HELD;
            }

            $conversation = Conversation::create([
                'publication_id' => $lockedPublication->id,
                'client_id' => $lockedClient->id,
                'author_id' => $lockedPublication->user_id,
                'status' => Conversation::STATUS_ACTIVE,
                'escrow_status' => $escrowStatus,
                'held_points' => $heldPoints,
                'last_message_at' => now(),
            ]);

            if ($escrowStatus === Conversation::ESCROW_HELD) {
                Transaction::create([
                    'conversation_id' => $conversation->id,
                    'payer_id' => $lockedClient->id,
                    'payee_id' => $lockedPublication->user_id,
                    'payer_name' => $lockedClient->name,
                    'payee_name' => $lockedPublication->user->name,
                    'publication_title' => $lockedPublication->title,
                    'type' => Transaction::TYPE_SERVICE,
                    'status' => Transaction::STATUS_HELD,
                    'amount' => $heldPoints,
                    'held_at' => now(),
                ]);
            }

            $conversation->messages()->create([
                'sender_id' => $lockedClient->id,
                'content' => $content,
            ]);

            return ['conversation' => $conversation, 'created' => true];
        }, 3);
    }
}
