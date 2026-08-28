@extends('layouts.app')

@section('title', trans('seeker::messages.my_publications'))

@include('seeker::_assets')

@section('content')
    <div class="seeker-public-shell">
    @include('seeker::_breadcrumb', ['breadcrumbs' => [['label' => trans('seeker::messages.my_publications')]]])
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        @include('seeker::_page-header', ['pageIcon' => 'bi-briefcase', 'pageTitle' => trans('seeker::messages.my_publications'), 'pageSubtitle' => trans('seeker::messages.my_publications_subtitle'), 'pageHeaderClass' => 'mb-0'])
        <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('seeker.profiles.show', auth()->user()) }}"><i class="bi bi-person-badge me-1" aria-hidden="true"></i>@lang('seeker::messages.profiles.my_profile')</a>@if($publicationsEnabled)<a class="btn btn-primary" href="{{ route('seeker.publications.create') }}"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> @lang('seeker::messages.publish')</a>@elseif($publishRestriction)<a class="btn btn-outline-warning" href="{{ route('seeker.restrictions.show', \Azuriom\Plugin\Seeker\Models\UserRestriction::TYPE_PUBLISH) }}"><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>@lang('seeker::messages.restrictions.details.view')</a>@endif</div>
    </div>

    @if($publications->isEmpty())
        <div class="seeker-empty-state"><span class="seeker-empty-icon"><i class="bi bi-megaphone" aria-hidden="true"></i></span><h2>@lang('seeker::messages.empty_mine')</h2><div class="mt-3">@if($publicationsEnabled)<a class="btn btn-primary" href="{{ route('seeker.publications.create') }}"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>@lang('seeker::messages.publish')</a>@elseif($publishRestriction)<a class="btn btn-outline-warning" href="{{ route('seeker.restrictions.show', \Azuriom\Plugin\Seeker\Models\UserRestriction::TYPE_PUBLISH) }}">@lang('seeker::messages.restrictions.details.view')</a>@endif</div></div>
    @else
        <div class="card seeker-table-card overflow-hidden">
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
                                        @if($publication->is_pinned)<span class="badge seeker-featured-badge"><i class="bi bi-pin-angle-fill me-1" aria-hidden="true"></i>@lang('seeker::messages.featured')</span>@endif
                                        <span class="badge text-bg-light">@include('seeker::publications._price', ['publication' => $publication])</span>
                                        <span class="badge {{ $publication->is_guest_visible ? 'text-bg-light' : 'text-bg-secondary' }}">@lang($publication->is_guest_visible ? 'seeker::messages.visibility.public' : 'seeker::messages.visibility.members')</span>
                                        <span class="badge text-bg-light">@include('seeker::publications._reputation', ['rating' => $publication->author_rating, 'count' => $publication->author_reviews_count])</span>
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
    </div>
@endsection
