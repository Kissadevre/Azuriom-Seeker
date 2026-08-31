@if(isset($breadcrumbs))
    @include('seeker::_breadcrumb', ['breadcrumbs' => $breadcrumbs])
@elseif(isset($backUrl))
    <a class="seeker-back-link" href="{{ $backUrl }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>{{ $backLabel }}</a>
@endif
<header class="seeker-page-header {{ $pageHeaderClass ?? '' }}">
    <span class="seeker-page-icon"><i class="bi {{ $pageIcon }}" aria-hidden="true"></i></span>
    <div class="min-w-0">
        <span class="seeker-eyebrow">Seeker</span>
        <h1>{{ $pageTitle }}</h1>
        @if(isset($pageSubtitle))
            <p>{{ $pageSubtitle }}</p>
        @endif
    </div>
</header>
