<?php

namespace Azuriom\Plugin\Seeker\Services;

use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Publication;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommissionCompletionService
{
    private const MAX_POINTS = 999999999999.99;

    public function request(Conversation $conversation, User $author, ?float $hours): Conversation
    {
        return DB::transaction(function () use ($conversation, $author, $hours) {
            $lockedConversation = Conversation::query()
                ->with('publication')
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            abort_unless($lockedConversation->author_id === $author->id, 403);
            $this->ensureCompletable($lockedConversation);
            abort_if($lockedConversation->completion_status === Conversation::COMPLETION_PENDING, 409);

            $proposedHours = $lockedConversation->isHourlyCommission() ? $hours : null;
            $this->calculateServicePoints($lockedConversation, $proposedHours);

            $lockedConversation->update([
                'completion_status' => Conversation::COMPLETION_PENDING,
                'proposed_hours' => $proposedHours,
                'service_points' => null,
                'tip_points' => 0,
                'final_message' => null,
                'completion_requested_at' => now(),
                'completion_responded_at' => null,
                'completed_at' => null,
            ]);

            return $lockedConversation;
        }, 3);
    }

    /** @return array{conversation: Conversation, service_points: float, tip_points: float} */
    public function confirm(Conversation $conversation, User $client, float $tipPoints, ?string $finalMessage): array
    {
        return DB::transaction(function () use ($conversation, $client, $tipPoints, $finalMessage) {
            $lockedConversation = Conversation::query()
                ->with('publication')
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            abort_unless($lockedConversation->client_id === $client->id, 403);
            $this->ensureCompletable($lockedConversation);
            abort_unless($lockedConversation->completion_status === Conversation::COMPLETION_PENDING, 409);

            $users = User::query()
                ->whereIn('id', [$lockedConversation->client_id, $lockedConversation->author_id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $lockedClient = $users->get($lockedConversation->client_id);
            $lockedAuthor = $users->get($lockedConversation->author_id);
            abort_if($lockedClient === null || $lockedAuthor === null, 404);

            $servicePoints = $this->calculateServicePoints(
                $lockedConversation,
                $lockedConversation->proposed_hours === null ? null : (float) $lockedConversation->proposed_hours
            );
            $tipPoints = round($tipPoints, 2);
            if ($servicePoints + $tipPoints > self::MAX_POINTS) {
                throw ValidationException::withMessages([
                    'tip_points' => trans('seeker::messages.completion.invalid_total'),
                ]);
            }
            $clientCharge = $lockedConversation->publication->price_basis === Publication::PRICE_BASIS_FIXED
                ? $tipPoints
                : $servicePoints + $tipPoints;

            if ((float) $lockedClient->money < $clientCharge) {
                throw ValidationException::withMessages([
                    'tip_points' => trans('seeker::messages.completion.insufficient_points', [
                        'points' => format_money($clientCharge),
                    ]),
                ]);
            }

            if ($lockedConversation->publication->price_basis === Publication::PRICE_BASIS_FIXED) {
                abort_unless($lockedConversation->escrow_status === Conversation::ESCROW_HELD, 409);
            }

            if ($clientCharge > 0) {
                $lockedClient->removeMoney($clientCharge);
            }
            $lockedAuthor->addMoney($servicePoints + $tipPoints);

            $lockedConversation->update([
                'status' => Conversation::STATUS_COMPLETED,
                'completion_status' => Conversation::COMPLETION_ACCEPTED,
                'escrow_status' => Conversation::ESCROW_RELEASED,
                'service_points' => $servicePoints,
                'tip_points' => $tipPoints,
                'final_message' => filled($finalMessage) ? trim($finalMessage) : null,
                'completion_responded_at' => now(),
                'completed_at' => now(),
            ]);

            return [
                'conversation' => $lockedConversation,
                'service_points' => $servicePoints,
                'tip_points' => $tipPoints,
            ];
        }, 3);
    }

    public function reject(Conversation $conversation, User $client): Conversation
    {
        return DB::transaction(function () use ($conversation, $client) {
            $lockedConversation = Conversation::query()
                ->with('publication')
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            abort_unless($lockedConversation->client_id === $client->id, 403);
            $this->ensureCompletable($lockedConversation);
            abort_unless($lockedConversation->completion_status === Conversation::COMPLETION_PENDING, 409);

            $lockedConversation->update([
                'completion_status' => Conversation::COMPLETION_REJECTED,
                'completion_responded_at' => now(),
            ]);

            return $lockedConversation;
        }, 3);
    }

    public function calculateServicePoints(Conversation $conversation, ?float $hours = null): float
    {
        $points = $conversation->publication->price_basis === Publication::PRICE_BASIS_FIXED
            ? (float) $conversation->held_points
            : (float) $conversation->publication->price * (float) $hours;
        $points = round($points, 2);

        if ($points <= 0 || $points > self::MAX_POINTS) {
            throw ValidationException::withMessages([
                'hours' => trans('seeker::messages.completion.invalid_total'),
            ]);
        }

        return $points;
    }

    private function ensureCompletable(Conversation $conversation): void
    {
        abort_unless(
            $conversation->status === Conversation::STATUS_ACTIVE
            && $conversation->isPaidCommission(),
            409
        );
    }
}
