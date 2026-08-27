<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), Transaction::statuses(), true)
            ? $request->query('status')
            : null;
        $type = in_array($request->query('type'), Transaction::types(), true)
            ? $request->query('type')
            : null;
        $userId = $request->integer('user_id') > 0 ? $request->integer('user_id') : null;

        $transactions = Transaction::query()
            ->with(['payer', 'payee', 'conversation.publication'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($userId, fn ($query) => $query->where(function ($query) use ($userId) {
                $query->where('payer_id', $userId)->orWhere('payee_id', $userId);
            }))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $statistics = [
            'spent' => Transaction::query()->where('status', Transaction::STATUS_COMPLETED)->sum('amount'),
            'held' => Transaction::query()->where('status', Transaction::STATUS_HELD)->sum('amount'),
            'services' => Transaction::query()
                ->where('status', Transaction::STATUS_COMPLETED)
                ->where('type', Transaction::TYPE_SERVICE)
                ->sum('amount'),
            'tips' => Transaction::query()
                ->where('status', Transaction::STATUS_COMPLETED)
                ->where('type', Transaction::TYPE_TIP)
                ->sum('amount'),
            'completed_count' => Transaction::query()->where('status', Transaction::STATUS_COMPLETED)->count(),
            'held_count' => Transaction::query()->where('status', Transaction::STATUS_HELD)->count(),
        ];

        return view('seeker::admin.transactions.index', compact(
            'transactions',
            'statistics',
            'status',
            'type',
            'userId'
        ));
    }
}
