@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.publications.title'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">@lang('seeker::admin.publications.title')</h1><p class="text-muted mb-0">@lang('seeker::admin.publications.subtitle')</p></div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-danger" href="{{ route('seeker.admin.reports.index') }}"><i class="bi bi-flag me-1" aria-hidden="true"></i>@lang('seeker::admin.reports.title')</a>
            <form method="GET">
                <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">@lang('seeker::admin.all_statuses')</option>
                @foreach(\Azuriom\Plugin\Seeker\Models\Publication::statuses() as $publicationStatus)
                    <option value="{{ $publicationStatus }}" @selected($status === $publicationStatus)>@lang('seeker::messages.statuses.'.$publicationStatus)</option>
                @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>@lang('seeker::admin.publication')</th><th>@lang('seeker::admin.author')</th><th>@lang('seeker::admin.status')</th><th>@lang('seeker::admin.created_at')</th><th>@lang('seeker::admin.updated_at')</th><th class="text-center">@lang('seeker::admin.conversations')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                <tbody>
                    @forelse($publications as $publication)
                        <tr>
                            <td><a class="fw-semibold text-decoration-none" href="{{ route('seeker.admin.publications.show', $publication) }}">{{ $publication->title }}</a><div class="small text-muted">@lang('seeker::messages.types.'.$publication->type)</div></td>
                            <td><a class="text-body text-decoration-none" href="{{ route('seeker.profiles.show', $publication->user) }}" target="_blank" rel="noopener">{{ $publication->user->name }}</a></td>
                            <td>@if($publication->trashed())<span class="badge text-bg-dark">@lang('seeker::admin.publications.removed')</span>@else<span class="badge {{ $publication->status === 'active' ? 'text-bg-success' : ($publication->status === 'hidden' ? 'text-bg-danger' : 'text-bg-secondary') }}">@lang('seeker::messages.statuses.'.$publication->status)</span>@endif</td>
                            <td class="text-nowrap">{{ format_date($publication->created_at, true) }}</td>
                            <td class="text-nowrap">{{ format_date($publication->updated_at, true) }}</td>
                            <td class="text-center"><a class="badge text-bg-primary text-decoration-none" href="{{ route('seeker.admin.publications.show', $publication) }}#conversations">{{ $publication->conversations_count }}</a></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.admin.publications.show', $publication) }}"><i class="bi bi-eye me-1" aria-hidden="true"></i>@lang('seeker::admin.details')</a>
                                    @unless($publication->trashed())<form method="POST" action="{{ route('seeker.admin.publications.status', $publication) }}" class="d-flex gap-2">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" aria-label="@lang('seeker::admin.set_status')">
                                            @foreach(\Azuriom\Plugin\Seeker\Models\Publication::statuses() as $publicationStatus)
                                                <option value="{{ $publicationStatus }}" @selected($publication->status === $publicationStatus)>@lang('seeker::messages.statuses.'.$publicationStatus)</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-primary">@lang('messages.actions.save')</button>
                                    </form>@endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">@lang('seeker::admin.empty')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($publications->hasPages())<div class="d-flex justify-content-center">{{ $publications->links() }}</div>@endif
@endsection
