<nav class="seeker-breadcrumb" aria-label="@lang('seeker::messages.breadcrumb.label')">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('seeker.index') }}">
                <i class="bi bi-grid" aria-hidden="true"></i>
                <span>@lang('seeker::messages.breadcrumb.home')</span>
            </a>
        </li>
        @foreach($breadcrumbs as $breadcrumb)
            @if(! $loop->last && isset($breadcrumb['url']))
                <li class="breadcrumb-item">
                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['label'] }}</li>
            @endif
        @endforeach
    </ol>
</nav>
