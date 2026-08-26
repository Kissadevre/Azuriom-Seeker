@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.title'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">@lang('seeker::admin.title')</h1><p class="text-muted mb-0">@lang('seeker::admin.subtitle')</p></div>
        <form method="GET">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">@lang('seeker::admin.all_statuses')</option>
                @foreach(\Azuriom\Plugin\Seeker\Models\Publication::statuses() as $publicationStatus)
                    <option value="{{ $publicationStatus }}" @selected($status === $publicationStatus)>@lang('seeker::messages.statuses.'.$publicationStatus)</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card shadow mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>@lang('seeker::admin.publication')</th><th>@lang('seeker::admin.author')</th><th>@lang('seeker::admin.type')</th><th>@lang('seeker::admin.status')</th><th>@lang('seeker::admin.created_at')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                <tbody>
                    @forelse($publications as $publication)
                        <tr>
                            <td class="fw-semibold">{{ $publication->title }}</td>
                            <td>{{ $publication->user->name }}</td>
                            <td>@lang('seeker::messages.types.'.$publication->type)</td>
                            <td><span class="badge {{ $publication->status === 'active' ? 'text-bg-success' : ($publication->status === 'hidden' ? 'text-bg-danger' : 'text-bg-secondary') }}">@lang('seeker::messages.statuses.'.$publication->status)</span></td>
                            <td>{{ format_date_compact($publication->created_at) }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.publications.show', $publication) }}" target="_blank">@lang('seeker::admin.view')</a>
                                    <form method="POST" action="{{ route('seeker.admin.publications.status', $publication) }}" class="d-flex gap-2">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" aria-label="@lang('seeker::admin.set_status')">
                                            @foreach(\Azuriom\Plugin\Seeker\Models\Publication::statuses() as $publicationStatus)
                                                <option value="{{ $publicationStatus }}" @selected($publication->status === $publicationStatus)>@lang('seeker::messages.statuses.'.$publicationStatus)</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-primary">@lang('messages.actions.save')</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">@lang('seeker::admin.empty')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($publications->hasPages())<div class="d-flex justify-content-center">{{ $publications->links() }}</div>@endif
@endsection
