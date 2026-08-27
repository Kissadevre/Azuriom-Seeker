@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.transactions.title'))

@include('seeker::admin._styles')

@section('content')
    <div class="seeker-admin-shell">
        @include('seeker::admin._header', [
            'headerIcon' => 'bi-arrow-left-right',
            'headerTitle' => trans('seeker::admin.transactions.title'),
            'headerSubtitle' => trans('seeker::admin.transactions.subtitle'),
            'headerTotal' => $transactions->total(),
        ])

        @php($transactionStats = [
            ['key' => 'spent', 'value' => $statistics['spent'], 'icon' => 'bi-wallet2', 'color' => 'primary', 'detail' => trans('seeker::admin.transactions.stats.completed_count', ['count' => $statistics['completed_count']])],
            ['key' => 'held', 'value' => $statistics['held'], 'icon' => 'bi-hourglass-split', 'color' => 'warning', 'detail' => trans('seeker::admin.transactions.stats.held_count', ['count' => $statistics['held_count']])],
            ['key' => 'services', 'value' => $statistics['services'], 'icon' => 'bi-briefcase', 'color' => 'success', 'detail' => null],
            ['key' => 'tips', 'value' => $statistics['tips'], 'icon' => 'bi-gift', 'color' => 'info', 'detail' => null],
        ])
        <div class="row g-3 mb-4">
            @foreach($transactionStats as $stat)
                <div class="col-sm-6 col-xl-3"><div class="seeker-admin-stat d-flex align-items-center justify-content-between gap-3"><div><div class="small text-body-secondary mb-1">@lang('seeker::admin.transactions.stats.'.$stat['key'])</div><div class="seeker-admin-stat-value">{{ format_money((float) $stat['value']) }}</div>@if($stat['detail'])<div class="small text-body-secondary mt-1">{{ $stat['detail'] }}</div>@endif</div><span class="seeker-admin-stat-icon text-{{ $stat['color'] }} bg-{{ $stat['color'] }} bg-opacity-10"><i class="bi {{ $stat['icon'] }}" aria-hidden="true"></i></span></div></div>
            @endforeach
        </div>

        <form method="GET" class="seeker-admin-toolbar mb-4">
            <div class="seeker-admin-toolbar-title"><i class="bi bi-funnel" aria-hidden="true"></i>@lang('seeker::admin.transactions.filters')</div>
            <div class="row g-2 align-items-end">
                <div class="col-md"><label class="form-label small fw-semibold" for="transactionStatus">@lang('seeker::admin.status')</label><select id="transactionStatus" name="status" class="form-select"><option value="">@lang('seeker::admin.transactions.all_statuses')</option>@foreach(\Azuriom\Plugin\Seeker\Models\Transaction::statuses() as $transactionStatus)<option value="{{ $transactionStatus }}" @selected($status === $transactionStatus)>@lang('seeker::admin.transactions.statuses.'.$transactionStatus)</option>@endforeach</select></div>
                <div class="col-md"><label class="form-label small fw-semibold" for="transactionType">@lang('seeker::admin.type')</label><select id="transactionType" name="type" class="form-select"><option value="">@lang('seeker::admin.transactions.all_types')</option>@foreach(\Azuriom\Plugin\Seeker\Models\Transaction::types() as $transactionType)<option value="{{ $transactionType }}" @selected($type === $transactionType)>@lang('seeker::admin.transactions.types.'.$transactionType)</option>@endforeach</select></div>
                <div class="col-md"><label class="form-label small fw-semibold" for="transactionUser">@lang('seeker::admin.transactions.user_id')</label><input id="transactionUser" type="number" min="1" name="user_id" value="{{ $userId }}" class="form-control" placeholder="@lang('seeker::admin.transactions.user_id')"></div>
                <div class="col-md-auto"><button class="btn btn-primary w-100"><i class="bi bi-funnel me-1" aria-hidden="true"></i>@lang('seeker::admin.transactions.filter')</button></div>
            </div>
        </form>

        <div class="card seeker-admin-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle seeker-admin-table mb-0">
                    <thead><tr><th>@lang('seeker::admin.transactions.reference')</th><th>@lang('seeker::admin.transactions.publication')</th><th>@lang('seeker::admin.transactions.type')</th><th>@lang('seeker::admin.transactions.payer')</th><th>@lang('seeker::admin.transactions.payee')</th><th>@lang('seeker::admin.transactions.amount')</th><th>@lang('seeker::admin.status')</th><th>@lang('seeker::admin.transactions.date')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="text-nowrap"><code class="px-2 py-1 rounded bg-primary bg-opacity-10 text-primary">#{{ $transaction->id }}</code></td>
                                <td style="min-width: 15rem"><div class="fw-semibold">{{ $transaction->publication_title }}</div>@if($transaction->conversation)<div class="small text-body-secondary mt-1">@lang('seeker::admin.transactions.conversation', ['id' => $transaction->conversation_id])</div>@endif</td>
                                <td><span class="badge rounded-pill text-bg-light"><i class="bi bi-{{ $transaction->type === 'tip' ? 'gift' : 'briefcase' }} me-1" aria-hidden="true"></i>@lang('seeker::admin.transactions.types.'.$transaction->type)</span></td>
                                <td><div class="fw-semibold">{{ $transaction->payer_name }}</div>@if($transaction->payer_id)<div class="small text-body-secondary">ID #{{ $transaction->payer_id }}</div>@endif</td>
                                <td><div class="fw-semibold">{{ $transaction->payee_name }}</div>@if($transaction->payee_id)<div class="small text-body-secondary">ID #{{ $transaction->payee_id }}</div>@endif</td>
                                <td class="fw-semibold text-nowrap">{{ format_money((float) $transaction->amount) }}</td>
                                <td><span class="badge text-bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'held' ? 'warning' : 'secondary') }} seeker-admin-status">@lang('seeker::admin.transactions.statuses.'.$transaction->status)</span></td>
                                <td class="text-nowrap text-body-secondary small">@if($transaction->completed_at){{ format_date($transaction->completed_at, true) }}@elseif($transaction->refunded_at){{ format_date($transaction->refunded_at, true) }}@elseif($transaction->held_at){{ format_date($transaction->held_at, true) }}@else{{ format_date($transaction->created_at, true) }}@endif</td>
                                <td class="text-end">@if($transaction->conversation)<div class="seeker-admin-action-group"><a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.admin.conversations.show', $transaction->conversation) }}" title="@lang('seeker::admin.details')" aria-label="@lang('seeker::admin.details')" data-bs-toggle="tooltip"><i class="bi bi-eye" aria-hidden="true"></i></a></div>@else<span class="text-body-secondary">@lang('seeker::admin.not_available')</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.transactions.empty')</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())<div class="seeker-admin-pagination">{{ $transactions->links() }}</div>@endif
        </div>
    </div>
@endsection
