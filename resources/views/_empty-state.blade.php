<div class="seeker-empty-state">
    <span class="seeker-empty-icon"><i class="bi {{ $emptyIcon }}" aria-hidden="true"></i></span>
    <h2>{{ $emptyTitle }}</h2>
    @if(isset($emptyText))<p>{{ $emptyText }}</p>@endif
    @if(isset($emptyActionUrl))
        <a class="btn btn-primary" href="{{ $emptyActionUrl }}">@if(isset($emptyActionIcon))<i class="bi {{ $emptyActionIcon }} me-1" aria-hidden="true"></i>@endif{{ $emptyActionLabel }}</a>
    @endif
</div>
