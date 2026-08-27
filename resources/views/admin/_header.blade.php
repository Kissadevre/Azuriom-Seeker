<header class="seeker-admin-header">
    <div class="seeker-admin-heading">
        <span class="seeker-admin-icon"><i class="bi {{ $headerIcon }}" aria-hidden="true"></i></span>
        <div>
            <h1 class="h3">{{ $headerTitle }}</h1>
            <p>{{ $headerSubtitle }}</p>
        </div>
    </div>
    @isset($headerTotal)
        <span class="seeker-admin-total"><i class="bi bi-collection" aria-hidden="true"></i>{{ $headerTotal }}</span>
    @endisset
</header>
