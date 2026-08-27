@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.transactions.title'))

@section('content')
    <div class="mb-4"><h1 class="h3 mb-1">@lang('seeker::admin.transactions.title')</h1><p class="text-muted mb-0">@lang('seeker::admin.transactions.subtitle')</p></div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="small text-muted">@lang('seeker::admin.transactions.stats.spent')</div><div class="h3 mb-1">{{ format_money((float) $statistics['spent']) }}</div><div class="small text-muted">@lang('seeker::admin.transactions.stats.completed_count', ['count' => $statistics['completed_count']])</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100 border-warning"><div class="card-body"><div class="small text-muted">@lang('seeker::admin.transactions.stats.held')</div><div class="h3 mb-1 text-warning">{{ format_money((float) $statistics['held']) }}</div><div class="small text-muted">@lang('seeker::admin.transactions.stats.held_count', ['count' => $statistics['held_count']])</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="small text-muted">@lang('seeker::admin.transactions.stats.services')</div><div class="h3 mb-0">{{ format_money((float) $statistics['services']) }}</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="small text-muted">@lang('seeker::admin.transactions.stats.tips')</div><div class="h3 mb-0">{{ format_money((float) $statistics['tips']) }}</div></div></div></div>
    </div>

    <form method="GET" class="card card-body mb-4">
        <div class="row g-2">
            <div class="col-md"><label class="visually-hidden" for="transactionStatus">@lang('seeker::admin.status')</label><select id="transactionStatus" name="status" class="form-select"><option value="">@lang('seeker::admin.transactions.all_statuses')</option>@foreach(\Azuriom\Plugin\Seeker\Models\Transaction::statuses() as $transactionStatus)<option value="{{ $transactionStatus }}" @selected($status === $transactionStatus)>@lang('seeker::admin.transactions.statuses.'.$transactionStatus)</option>@endforeach</select></div>
            <div class="col-md"><label class="visually-hidden" for="transactionType">@lang('seeker::admin.type')</label><select id="transactionType" name="type" class="form-select"><option value="">@lang('seeker::admin.transactions.all_types')</option>@foreach(\Azuriom\Plugin\Seeker\Models\Transaction::types() as $transactionType)<option value="{{ $transactionType }}" @selected($type === $transactionType)>@lang('seeker::admin.transactions.types.'.$transactionType)</option>@endforeach</select></div>
            <div class="col-md"><label class="visually-hidden" for="transactionUser">@lang('seeker::admin.transactions.user_id')</label><input id="transactionUser" type="number" min="1" name="user_id" value="{{ $userId }}" class="form-control" placeholder="@lang('seeker::admin.transactions.user_id')"></div>
            <div class="col-md-auto"><button class="btn btn-primary w-100"><i class="bi bi-funnel me-1" aria-hidden="true"></i>@lang('seeker::admin.transactions.filter')</button></div>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>@lang('seeker::admin.transactions.reference')</th><th>@lang('seeker::admin.transactions.publication')</th><th>@lang('seeker::admin.transactions.type')</th><th>@lang('seeker::admin.transactions.payer')</th><th>@lang('seeker::admin.transactions.payee')</th><th>@lang('seeker::admin.transactions.amount')</th><th>@lang('seeker::admin.status')</th><th>@lang('seeker::admin.transactions.date')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="text-nowrap">#{{ $transaction->id }}</td>
                            <td style="min-width: 14rem"><div class="fw-semibold">{{ $transaction->publication_title }}</div>@if($transaction->conversation)<div class="small text-muted">@lang('seeker::admin.transactions.conversation', ['id' => $transaction->conversation_id])</div>@endif</td>
                            <td>@lang('seeker::admin.transactions.types.'.$transaction->type)</td>
                            <td><div>{{ $transaction->payer_name }}</div>@if($transaction->payer_id)<div class="small text-muted">ID #{{ $transaction->payer_id }}</div>@endif</td>
                            <td><div>{{ $transaction->payee_name }}</div>@if($transaction->payee_id)<div class="small text-muted">ID #{{ $transaction->payee_id }}</div>@endif</td>
                            <td class="fw-semibold text-nowrap">{{ format_money((float) $transaction->amount) }}</td>
                            <td><span class="badge text-bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'held' ? 'warning' : 'secondary') }}">@lang('seeker::admin.transactions.statuses.'.$transaction->status)</span></td>
                            <td class="text-nowrap">@if($transaction->completed_at){{ format_date($transaction->completed_at, true) }}@elseif($transaction->refunded_at){{ format_date($transaction->refunded_at, true) }}@elseif($transaction->held_at){{ format_date($transaction->held_at, true) }}@else{{ format_date($transaction->created_at, true) }}@endif</td>
                            <td class="text-end">@if($transaction->conversation)<a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.admin.conversations.show', $transaction->conversation) }}"><i class="bi bi-eye me-1" aria-hidden="true"></i>@lang('seeker::admin.details')</a>@else<span class="text-muted">@lang('seeker::admin.not_available')</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-5">@lang('seeker::admin.transactions.empty')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())<div class="d-flex justify-content-center mt-4">{{ $transactions->links() }}</div>@endif
@endsection
