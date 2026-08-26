@extends('layouts.app')

@section('title', trans('seeker::messages.my_publications'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="h2 mb-0">@lang('seeker::messages.my_publications')</h1>
        <a class="btn btn-primary" href="{{ route('seeker.publications.create') }}"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> @lang('seeker::messages.publish')</a>
    </div>

    @if($publications->isEmpty())
        <div class="card"><div class="card-body py-5 text-center"><p class="text-muted">@lang('seeker::messages.empty_mine')</p><a class="btn btn-primary" href="{{ route('seeker.publications.create') }}">@lang('seeker::messages.publish')</a></div></div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>@lang('seeker::messages.fields.title')</th><th>@lang('seeker::messages.fields.type')</th><th>@lang('seeker::messages.fields.status')</th><th class="text-end"></th></tr></thead>
                    <tbody>
                        @foreach($publications as $publication)
                            <tr>
                                <td>
                                    <a class="fw-semibold text-decoration-none" href="{{ route('seeker.publications.show', $publication) }}">{{ $publication->title }}</a>
                                    <div class="small text-muted">{{ format_date_compact($publication->created_at) }}</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="badge text-bg-light">@include('seeker::publications._price', ['publication' => $publication])</span>
                                        <span class="badge {{ $publication->is_guest_visible ? 'text-bg-light' : 'text-bg-secondary' }}">@lang($publication->is_guest_visible ? 'seeker::messages.visibility.public' : 'seeker::messages.visibility.members')</span>
                                    </div>
                                </td>
                                <td>@lang('seeker::messages.types.'.$publication->type)</td>
                                <td><span class="badge {{ $publication->status === 'active' ? 'text-bg-success' : ($publication->status === 'hidden' ? 'text-bg-danger' : 'text-bg-secondary') }}">@lang('seeker::messages.statuses.'.$publication->status)</span></td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.publications.edit', $publication) }}"><i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">@lang('seeker::messages.edit')</span></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($publications->hasPages())<div class="d-flex justify-content-center mt-4">{{ $publications->links() }}</div>@endif
    @endif
@endsection
