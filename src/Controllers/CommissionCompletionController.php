<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Requests\ConfirmCommissionCompletionRequest;
use Azuriom\Plugin\Seeker\Requests\RequestCommissionCompletionRequest;
use Azuriom\Plugin\Seeker\Services\CommissionCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionCompletionController extends Controller
{
    public function requestCompletion(
        RequestCommissionCompletionRequest $request,
        Conversation $conversation,
        CommissionCompletionService $completionService
    ): RedirectResponse {
        $conversation = $completionService->request(
            $conversation,
            $request->user(),
            $request->validated('hours') === null ? null : (float) $request->validated('hours')
        );
        $conversation->loadMissing('client');

        rescue(function () use ($request, $conversation) {
            (new AlertNotification(trans('seeker::messages.notifications.completion_requested', [
                'publication' => $conversation->publication->title,
            ])))
                ->link(route('seeker.conversations.completion.show', $conversation, false))
                ->from($request->user())
                ->send($conversation->client);
        }, report: true);

        return to_route('seeker.conversations.show', $conversation)
            ->with('success', trans('seeker::messages.completion.requested'));
    }

    public function show(
        Request $request,
        Conversation $conversation,
        CommissionCompletionService $completionService
    ): View|RedirectResponse
    {
        abort_unless($conversation->client_id === $request->user()->id, 403);
        $conversation->load(['publication', 'author']);

        if ($conversation->status !== Conversation::STATUS_ACTIVE
            || $conversation->completion_status !== Conversation::COMPLETION_PENDING) {
            return to_route('seeker.conversations.show', $conversation);
        }

        abort_unless(
            $conversation->isPaidCommission(),
            409
        );

        $servicePoints = $completionService->calculateServicePoints(
            $conversation,
            $conversation->proposed_hours === null ? null : (float) $conversation->proposed_hours
        );

        return view('seeker::conversations.completion', compact('conversation', 'servicePoints'));
    }

    public function confirm(
        ConfirmCommissionCompletionRequest $request,
        Conversation $conversation,
        CommissionCompletionService $completionService
    ): RedirectResponse {
        $result = $completionService->confirm(
            $conversation,
            $request->user(),
            (float) ($request->validated('tip_points') ?? 0),
            $request->validated('final_message')
        );
        $completedConversation = $result['conversation'];
        $completedConversation->loadMissing('author');

        rescue(function () use ($request, $completedConversation, $result) {
            (new AlertNotification(trans('seeker::messages.notifications.completion_confirmed', [
                'points' => format_money($result['service_points'] + $result['tip_points']),
            ])))
                ->link(route('seeker.conversations.show', $completedConversation, false))
                ->from($request->user())
                ->send($completedConversation->author);
        }, report: true);

        return to_route('seeker.conversations.show', $completedConversation)
            ->with('success', trans('seeker::messages.completion.confirmed'));
    }

    public function reject(
        Request $request,
        Conversation $conversation,
        CommissionCompletionService $completionService
    ): RedirectResponse {
        $conversation = $completionService->reject($conversation, $request->user());
        $conversation->loadMissing('author');

        rescue(function () use ($request, $conversation) {
            (new AlertNotification(trans('seeker::messages.notifications.completion_rejected', [
                'publication' => $conversation->publication->title,
            ])))
                ->link(route('seeker.conversations.show', $conversation, false))
                ->from($request->user())
                ->send($conversation->author);
        }, report: true);

        return to_route('seeker.conversations.show', $conversation)
            ->with('error', trans('seeker::messages.completion.rejected'));
    }
}
