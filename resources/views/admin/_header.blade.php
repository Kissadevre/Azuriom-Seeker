<header class="seeker-admin-header {{ isset($headerTone) ? 'is-'.$headerTone : '' }}">
    <div class="seeker-admin-heading">
        <span class="seeker-admin-icon"><i class="bi {{ $headerIcon }}" aria-hidden="true"></i></span>
        <div>
            <span class="seeker-admin-eyebrow">{{ $headerEyebrow ?? trans('seeker::admin.title') }}</span>
            <h1 class="h3">{{ $headerTitle }}</h1>
            <p>{{ $headerSubtitle }}</p>
        </div>
    </div>
    @isset($headerTotal)
        <span class="seeker-admin-total">
            <i class="bi {{ $headerTotalIcon ?? 'bi-collection' }}" aria-hidden="true"></i>
            {{ $headerTotal }}
        </span>
    @endisset
</header>
