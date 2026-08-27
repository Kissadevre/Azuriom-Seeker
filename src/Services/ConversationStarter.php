<?php

namespace Azuriom\Plugin\Seeker\Services;

use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Publication;
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

            if ($lockedPublication->pricing_type === Publication::PRICING_POINTS
                && $lockedPublication->price_basis === Publication::PRICE_BASIS_FIXED) {
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

            $conversation->messages()->create([
                'sender_id' => $lockedClient->id,
                'content' => $content,
            ]);

            return ['conversation' => $conversation, 'created' => true];
        }, 3);
    }
}
